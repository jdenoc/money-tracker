<?php

namespace Tests\Feature\Console;

use App\Account;
use App\AccountType;
use App\Entry;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class AccountTotalSanityCheckTest extends TestCase {

    use WithFaker;

    private $_command = 'sanity-check:account-total';
    private $_screen_only_notification_options = ['--notify-screen'=>true, '--dont-notify-discord'=>true];

    private static $TEMPLATE_CHECKING_ACCOUNT_OK = "Checking account ID:%d\n\tOK";
    private static $TEMPLATE_ACCOUNT_NOT_FOUND = "Account %d not found";

    public function setUp(): void{
        parent::setUp();
        $this->withoutMockingConsoleOutput();
    }

    public function testForceFailureOutputtingToScreenAndWithoutNotifyingDiscord(){
        Artisan::call($this->_command, array_merge(['--force-failure'=>true], $this->_screen_only_notification_options));

        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("Forcing Failure", $result_as_text);
        $this->assertStringContainsString("Sanity check has failed", $result_as_text);
    }

    public function testSanityCheckOutputtingToScreenAndWithoutNotifyingDiscord(){
        $accounts = factory(Account::class, 3)->create(['disabled'=>true, 'total'=>0]);
        $account_types = collect();
        foreach($accounts as $account){
            $account_type = factory(AccountType::class)->create(['account_id'=>$account->id, 'disabled'=>0]);
            factory(Entry::class, 2)->create(['account_type_id'=>$account_type->id, 'disabled'=>0]);
            $account_types->push($account_type);
        }

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        foreach($accounts as $account){
            $this->assertStringContainsString(sprintf(self::$TEMPLATE_CHECKING_ACCOUNT_OK, $account->id), $result_as_text);
        }
    }

    public function testSanityCheckIndividualAccountIdOutputtingToScreenAndWithoutNotifyingDiscord(){
        $this->seedDatabaseAndMaybeTruncateTable('entries');

        $accounts = Account::where('disabled', 0);
        $accounts->update(['total'=>0]);
        $account = $accounts->get()->random();
        $account_type = AccountType::where(['account_id'=>$account->id, 'disabled'=>0])->get()->random();
        $entries = factory(Entry::class, 10)->create(['account_type_id'=>$account_type->id, 'disabled'=>0]);

        $new_total = $entries->where('disabled', 0)
            ->sum(function($entry){
                return ($entry['expense'] ? -1 : 1) * $entry['entry_value'];
            });
        $account->update(['total'=>$new_total]);

        Artisan::call($this->_command, array_merge(['accountId'=>$account->id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_CHECKING_ACCOUNT_OK, $account->id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdNotFoundOutputtingToScreenAndWithoutNotifyingDiscord(){
        $this->seedDatabaseAndMaybeTruncateTable('accounts');

        $account_id = $this->faker->randomDigitNotZero();
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdZeroNotFoundOutputtingToScreenAndWithoutNotifyingDiscord(){
        $this->seedDatabaseAndMaybeTruncateTable();

        $account_id = 0;
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckAccountsNotFoundOutputtingToScreenAndWithoutNotifyingDiscord(){
        $this->seedDatabaseAndMaybeTruncateTable('accounts');

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("No accounts found", $result_as_text);
    }

    /**
     * @param string|null $table_to_truncate
     */
    private function seedDatabaseAndMaybeTruncateTable($table_to_truncate = null){
        Artisan::call("db:seed", ['--class'=>'UiSampleDatabaseSeeder']);
        if(!is_null($table_to_truncate)){
            DB::table($table_to_truncate)->truncate();
        }
    }

}
