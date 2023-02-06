<?php

namespace Tests\Feature\Console;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Entry;
use App\Traits\Tests\TruncateDatabaseTables;
use App\Traits\Tests\DatabaseMigrations;
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
        $this->migrate();
        $this->withoutMockingConsoleOutput();
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
            $account_type = AccountType::factory()->for($account)->create();
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
        DB::table(Entry::getTableName())->truncate();

        $accounts = Account::all(); // only active accounts
        $accounts->each(function(Account $account) {
            $account->total = 0;
            $account->save();
        });
        $account = $accounts->random();

        $account_type = AccountType::all()->where('account_id', $account->id)->random();    // only active account-types
        $entries = Entry::factory()->count(10)->for($account_type)->create(['disabled'=>false]);
        $new_account_total = $entries
            ->where('disabled', false)
            ->sum(function(Entry $entry) {
                return ($entry['expense'] ? -1 : 1) * $entry['entry_value'];
            });
        $account->total = $new_account_total;
        $account->save();

        Artisan::call($this->_command, array_merge(['accountId'=>$account->id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_CHECKING_ACCOUNT_OK, $account->id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        DB::table(Account::getTableName())->truncate();

        $account_id = $this->faker->randomDigitNotZero();
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdZeroNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        $account_id = 0;
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString(sprintf(self::$TEMPLATE_ACCOUNT_NOT_FOUND, $account_id), $result_as_text);
    }

    public function testSanityCheckAccountsNotFoundOutputtingToScreenAndWithoutNotifyingDiscord() {
        DB::table(Account::getTableName())->truncate();

        Artisan::call($this->_command, $this->_screen_only_notification_options);
        $result_as_text = trim(Artisan::output());
        $this->assertStringContainsString("No accounts found", $result_as_text);
    }

}
