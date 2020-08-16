<?php

namespace App\Console\Commands;

use App\Account;
use App\AccountTotalSanityCheck as SanityCheckAlertObject;
use Eklundkristoffer\DiscordWebhook\DiscordClient;
use Eklundkristoffer\DiscordWebhook\DiscordContentObject;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AccountTotalSanityCheck extends Command {

    const CONFIG_ENV = "app.env";
    const CONFIG_DISCORD_WEBHOOK_URL ="services.discord.webhook_url";
    const ARG_ACCOUNT_ID = "accountId";
    const OPTION_FORCE_FAILURE = 'force-failure';
    const OPTION_DONT_NOTIFY_DISCORD = 'dont-notify-discord';
    const OPTION_NOTIFY_SCREEN = "notify-screen";
    const WEBHOOK_ALIAS = "account-total-sanity-check-failure";

    const LOG_LEVEL_DEBUG = 'debug';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_EMERGENCY = 'emergency';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity-check:account-total
                            {accountId? : Account ID to perform sanity check}
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
            $this->notifyInternally("Forcing Failure", self::LOG_LEVEL_DEBUG);

            $sanity_check_object = new SanityCheckAlertObject();
            $sanity_check_object->account_id = 0;
            $sanity_check_object->account_name = 'Forced Failure';
            $sanity_check_object->actual = 0;
            $sanity_check_object->expected = 1;

            $this->notifySanityCheck($sanity_check_object);
        } else {
            $account_id = $this->argument(self::ARG_ACCOUNT_ID);
            if(!is_null($account_id)){
                $account = Account::find($account_id);
                if(is_null($account)){
                    $this->notifyInternally(sprintf("Account %d not found", $account_id), self::LOG_LEVEL_WARNING);
                } else {
                    $sanity_check_object = $this->retrieveExpectedAccountTotalData($account);
                    $this->notifySanityCheck($sanity_check_object);
                }
            } elseif(!is_null($account_id) && (int)$account_id === 0){
                $this->notifyInternally("Account 0 does not exist", self::LOG_LEVEL_WARNING);
            } else {
                $accounts = Account::all();
                if($accounts->isEmpty()){
                    $this->notifyInternally("No accounts found", self::LOG_LEVEL_WARNING);
                    $webhook_data = New DiscordContentObject();
                    $webhook_data->addEmbeddedTitle("`[".strtoupper(config(self::CONFIG_ENV))."]` Account Total: Sanity Check | WARNING");
                    $webhook_data->addEmbeddedDescription("No accounts found");
                    $this->notifyDiscord($webhook_data, self::LOG_LEVEL_WARNING);
                } else {
                    foreach($accounts as $account){
                        $sanity_check_object = $this->retrieveExpectedAccountTotalData($account);
                        $this->notifySanityCheck($sanity_check_object);
                    }
                }
            }
        }
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

        $this->notifyInternally("Checking account ID:".$account->id, self::LOG_LEVEL_DEBUG);
        $sanity_check_result = $sanity_check_query->get();
        $sanity_check_object = new SanityCheckAlertObject();
        $sanity_check_object->actual = $sanity_check_result[0]->actual;
        $sanity_check_object->expected = $account->total;
        $sanity_check_object->account_id = $account->id;
        $sanity_check_object->account_name = $account->name;
        return $sanity_check_object;
    }

    /**
     * Send a "notification" regarding a difference (if any) between actual and expected values
     * @param SanityCheckAlertObject $sanity_check_object
     */
    private function notifySanityCheck($sanity_check_object){
        if($sanity_check_object->diff() > 0){
            $this->notifyInternally("Sanity check has failed", self::LOG_LEVEL_EMERGENCY);
            $this->notifyInternally($sanity_check_object, self::LOG_LEVEL_EMERGENCY);
            $webhook_data = New DiscordContentObject();
            $webhook_data->addEmbeddedTitle("`[".strtoupper(config(self::CONFIG_ENV))."]` Account Total: Sanity Check | Failure");
            $webhook_data->addEmbeddedDescription("Sanity check has failed for account: _`".$sanity_check_object->account_name."`_ `[".$sanity_check_object->account_id."]`");
            $webhook_data->addEmbeddedField("Actual", '**`'.$sanity_check_object->actual.'`**');
            $webhook_data->addEmbeddedField("Expected", '**`'.$sanity_check_object->expected.'`**');
            $webhook_data->addEmbeddedField("Difference", '**`'.$sanity_check_object->diff().'`**');
            $this->notifyDiscord($webhook_data, self::LOG_LEVEL_EMERGENCY);
        } else {
            $this->notifyInternally("\tOK");
        }
    }

    /**
     * Send "notification" to Discord Webhook
     * @param DiscordContentObject $webhook_data
     * @param string $level
     */
    private function notifyDiscord($webhook_data, $level=self::LOG_LEVEL_DEBUG){
        if(!$this->option(self::OPTION_DONT_NOTIFY_DISCORD)){
            switch($level){
                case self::LOG_LEVEL_DEBUG:
                default:
                    $webhook_data->addEmbeddedColor($webhook_data::COLOR_DEFAULT);
                    break;
                case self::LOG_LEVEL_WARNING:
                    $webhook_data->addEmbeddedColor($webhook_data::COLOR_ORANGE);
                    break;
                case self::LOG_LEVEL_EMERGENCY:
                    $webhook_data->addEmbeddedColor($webhook_data::COLOR_RED);
                    break;
            }

            $discord_webhook_url = config(self::CONFIG_DISCORD_WEBHOOK_URL);
            if(empty($discord_webhook_url)){
                $this->notifyInternally("Discord webhook URL not set. Can not send notification to Discord", self::LOG_LEVEL_WARNING);
            } else {
                // Attempt to notify Discord
                $discord = new DiscordClient();
                $discord->registerWebhook(self::WEBHOOK_ALIAS, $discord_webhook_url);
                $discord->executeWebhook(self::WEBHOOK_ALIAS, $webhook_data->toArray());
            }
        }
    }

    /**
     * Log a message in a log file
     * Or if option is enabled, to screen
     * @param string $notification_message
     * @param string $level
     */
    private function notifyInternally($notification_message, $level=self::LOG_LEVEL_DEBUG){
        logger()->log($level, $notification_message);
        if($this->option(self::OPTION_NOTIFY_SCREEN)){
            switch($level){
                case self::LOG_LEVEL_DEBUG:
                default:
                    $this->info($notification_message);
                    break;
                case self::LOG_LEVEL_WARNING:
                    $this->warn($notification_message);
                    break;
                case self::LOG_LEVEL_EMERGENCY:
                    $this->error($notification_message);
                    break;
            }
        }
    }
}
