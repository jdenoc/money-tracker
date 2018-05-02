<?php

namespace App\Console\Commands;

use App\Account;
use App\AccountTotalSanityCheck as SanityCheckAlertObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Eklundkristoffer\DiscordWebhook\DiscordClient;

class AccountTotalSanityCheck extends Command {

    const CONFIG_ENV = "app.env";
    const CONFIG_DISCORD_WEBHOOK_URL ="services.discord.webhook_url";
    const OPTION_FORCE_FAILURE = 'force-failure';
    const OPTION_DONT_NOTIFY_DISCORD = 'dont-notify-discord';
    const OPTION_NOTIFY_SCREEN = "notify-screen";
    const WEBHOOK_ALIAS = "account-total-sanity-check-failure";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity-check:account-total
                            {--force-failure : Force sanity check to fail before even performing it}
                            {--dont-notify-discord : Stop script from sending notification to provided Discord webhook}
                            {--notify-screen : Output to screen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provides a sanity check on the account total value stored in the database compared to that of the overall value compiled by the relevant associated entries';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(){
        if($this->option(self::OPTION_FORCE_FAILURE)){
            $this->notifyInternally("Forcing Failure", 'debug');

            $sanity_check_object = new SanityCheckAlertObject();
            $sanity_check_object->account_id = 0;
            $sanity_check_object->account_name = 'Forced Failure';
            $sanity_check_object->actual = 0;
            $sanity_check_object->expected = 1;

            $this->notify($sanity_check_object);
        } else {
            $accounts = Account::all();
            foreach($accounts as $account){
                $sanity_check_object = $this->retrieveExpectedAccountTotalData($account);
                $this->notify($sanity_check_object);
            }
        }
        return;
    }

    /**
     * make MySQL calls to retrieve data and perform account total sanity check
     * @param Account $account
     * @return SanityCheckAlertObject
     */
    private function retrieveExpectedAccountTotalData($account){
        $sanity_check_query = DB::table('entries')
            ->select(DB::raw("IFNULL( SUM( IF( entries.expense=1, -1*entries.entry_value, entries.entry_value ) ), 0 ) as actual"))
            ->join('account_types', function($join) use ($account){
                $join->on('entries.account_type_id', '=', 'account_types.id')
                    ->where('account_types.account_id', $account->id);
            })
            ->where('entries.disabled', '0')
            ->orderBy(DB::raw('entries.entry_date desc, entries.id'), 'desc');
        /**
         * The above stuff is translated into MySQL here:
            SELECT
              IFNULL( SUM( IF( entries.expense=1, -1*entries.entry_value, entries.entry_value ) ), 0 ) as actual
            FROM entries
            INNER JOIN account_types
              ON entries.account_type_id = account_types.id
              AND account_types.account_id = $account->id
            WHERE entries.disabled = 0
            ORDER BY entries.entry_date DESC, entries.id DESC
         */

        $this->notifyInternally("Checking account ID:".$account->id, "debug");
        $sanity_check_result = $sanity_check_query->get();
        $sanity_check_object = new SanityCheckAlertObject();
        $sanity_check_object->actual = $sanity_check_result[0]->actual;
        $sanity_check_object->expected = $account->total;
        $sanity_check_object->account_id = $account->id;
        $sanity_check_object->account_name = $account->name;
        return $sanity_check_object;
    }

    /**
     * Send a "notification" if there is a difference between actual and expected values
     * @param SanityCheckAlertObject $sanity_check_object
     */
    private function notify($sanity_check_object){
        if($sanity_check_object->diff() > 0){
            $this->notifyInternally("Sanity check has failed ".$sanity_check_object, 'emergency');

            // Attempt to notify Discord
            if(!$this->option(self::OPTION_DONT_NOTIFY_DISCORD)){
                $discord_webhook_url = config(self::CONFIG_DISCORD_WEBHOOK_URL);
                if(empty($discord_webhook_url)){
                    $this->notifyInternally("Discord webhook URL not set. Can not send notification to Discord", 'warning');
                } else {
                    $this->notifyDiscord($sanity_check_object, $discord_webhook_url);
                }
            }
        }
    }

    /**
     * Send "notification" to Discord Webhook
     * @param SanityCheckAlertObject $sanity_check
     * @param string $webhook_url
     */
    private function notifyDiscord($sanity_check, $webhook_url){
        $webhook_data = [
            "embeds"=>[[
                "title"=>"`[".strtoupper(config(self::CONFIG_ENV))."]` Account Total: Sanity Check | Failure",
                "description"=>"Sanity check has failed for account: _`".$sanity_check->account_name."`_ `[".$sanity_check->account_id."]`",
                "color"=>15158332,  // RED
                "fields"=>[
                    ["name"=>"Actual", "value"=>'**`'.$sanity_check->actual.'`**', "inline"=>true],
                    ["name"=>"Expected", "value"=>'**`'.$sanity_check->expected.'`**', "inline"=>true],
                    ["name"=>"Difference", "value"=>'**`'.$sanity_check->diff().'`**', "inline"=>true]
                ],
                "timestamp"=>date("c")
            ]]
        ];

        $discord = new DiscordClient();
        $discord->registerWebhook(self::WEBHOOK_ALIAS, $webhook_url);
        $discord->executeWebhook(self::WEBHOOK_ALIAS, $webhook_data);
    }

    /**
     * Log a message in a log file
     * Or if option is enabled, to screen
     * @param string $notification_message
     * @param string $level
     */
    private function notifyInternally($notification_message, $level='debug'){
        Log::log($level, $notification_message);
        if($this->option(self::OPTION_NOTIFY_SCREEN)){
            switch($level){
                case 'debug':
                    $this->info($notification_message);
                    break;
                case 'warning':
                    $this->warn($notification_message);
                    break;
                case 'emergency':
                    $this->error($notification_message);
                    break;
            }
        }
    }
}
