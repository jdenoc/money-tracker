<?php

namespace Tests\Feature\Console;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use App\Traits\Tests\TruncateDatabaseTables;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class AccountTotalSanityCheckTest extends TestCase {
    use DatabaseMigrations;
    use TruncateDatabaseTables;
    use WithFaker;

    private string $_command = 'sanity-check:account-total';
    private $_screen_only_notification_options = ['--notify-screen'=>true, '--dont-notify-discord'=>true];

    private static string $TEMPLATE_CHECKING_ACCOUNT_OK = "Checking account ID:%d\n\tOK";
    private static string $TEMPLATE_ACCOUNT_NOT_FOUND = "Account %d not found";

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
        $accounts = Account::factory()->count(3)->disabled()->create(['total'=>0]);
        $account_types = collect();
        foreach ($accounts as $account) {
            $account_type = AccountType::factory()->for($account)->create([AccountType::DELETED_AT=>null]);
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
        $this->seedDatabaseAndMaybeTruncateTable(Entry::getTableName());

        $accounts = Account::whereNull(Account::DELETED_AT); // only active accounts
        $accounts->update(['total'=>0]);
        $account = $accounts->get()->random();
        $account_type = AccountType::where(['account_id'=>$account->id, 'disabled'=>0])->get()->random();
        $entries = Entry::factory()->count(10)->create(['account_type_id'=>$account_type->id, 'disabled'=>0]);

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
        $this->seedDatabaseAndMaybeTruncateTable(Account::getTableName());

        $account_id = $this->faker->randomDigitNotZero();
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdZeroNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabaseAndMaybeTruncateTable();

        $account_id = 0;
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckAccountsNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $this->seedDatabaseAndMaybeTruncateTable(Account::getTableName());

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("No accounts found", $result_as_text);
    }

    private function seedDatabaseAndMaybeTruncateTable(?string $table_to_truncate = null) {
        Artisan::call("db:seed", ['--class'=>'UiSampleDatabaseSeeder']);
        if (!is_null($table_to_truncate)) {
            DB::table($table_to_truncate)->truncate();
        }
    }

}
