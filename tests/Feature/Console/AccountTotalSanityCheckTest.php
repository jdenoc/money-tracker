<?php

namespace Tests\Feature\Console;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use App\Traits\Tests\TruncateDatabaseTables;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class AccountTotalSanityCheckTest extends TestCase {
    use DatabaseMigrations;
    use TruncateDatabaseTables;

    private $_command = 'sanity-check:account-total';
    private $_screen_only_notification_options = ['--notify-screen'=>true, '--dont-notify-discord'=>true];

    private static $TEMPLATE_CHECKING_ACCOUNT_OK = "Checking account ID:%d\n\tOK";
    private static $TEMPLATE_ACCOUNT_NOT_FOUND = "Account %d not found";

    public function setUp(): void {
        parent::setUp();
        $this->withoutMockingConsoleOutput();
    }

    public function tearDown(): void {
        $this->truncateDatabaseTables(['migrations']);
        parent::tearDown();
    }

    /**
     * override the RefreshDatabase trait method to prevent the use of said trait in THIS test suite
     */
    public function refreshTestDatabase(): void {
    }

    public function testForceFailureOutputtingToScreenAndWithoutNotifyingDiscord() {
        Artisan::call($this->_command, array_merge(['--force-failure'=>true], $this->_screen_only_notification_options));

        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("Forcing Failure", $result_as_text);
        $this->assertStringContainsString("Sanity check has failed", $result_as_text);
    }

    public function testSanityCheckOutputtingToScreenAndWithoutNotifyingDiscord() {
        $accounts = Account::factory()->count(3)->create(['total'=>0]);
        $account_types = collect();
        foreach ($accounts as $account) {
            $account_type = AccountType::factory()->for($account)->create(['disabled'=>0]);
            Entry::factory()->count(2)->for($account_type)->create(['disabled'=>0]);
            $account_types->push($account_type);
        }

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        foreach ($accounts as $account) {
            $this->assertStringContainsString(sprintf(self::$TEMPLATE_CHECKING_ACCOUNT_OK, $account->id), $result_as_text);
        }
    }

    public function testSanityCheckIndividualAccountIdOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabase();
        $this->truncateTable('entries');

        $account = Account::get()->random();
        $account->update(['total'=>0]);
        $account_type = AccountType::where(['account_id'=>$account->id, 'disabled'=>0])->get()->random();
        $entries = Entry::factory()->for($account_type)->count(10)->create(['disabled'=>0]);

        $new_total = $entries->where('disabled', 0)
            ->sum(function($entry) {
                return ($entry['expense'] ? -1 : 1) * $entry['entry_value'];
            });
        $account->update(['total'=>$new_total]);

        Artisan::call($this->_command, array_merge(['accountId'=>$account->id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_CHECKING_ACCOUNT_OK, $account->id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabase();
        $this->truncateTable(Account::getTableName());

        $account_id = fake()->randomDigitNotZero();
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdZeroNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabase();

        $account_id = 0;
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckAccountsNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabase();
        $this->truncateTable(Account::getTableName());

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("No accounts found", $result_as_text);
    }

    private function seedDatabase() {
        Artisan::call("db:seed", ['--class'=>'UiSampleDatabaseSeeder']);
    }

    private function truncateTable(string $table_to_truncate) {
        DB::table($table_to_truncate)->truncate();
    }

}
