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

    public function testForceFailureOutputtingToScreenAndWithoutNotifyingDiscord(){
        Artisan::call($this->_command, array_merge(['--force-failure'=>true], $this->_screen_only_notification_options));

        $result_as_text = trim(Artisan::output());
        $this->assertContains("Forcing Failure", $result_as_text);
        $this->assertContains("Sanity check has failed", $result_as_text);
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
            $this->assertContains(sprintf("Checking account ID:%d\n\tOK", $account->id), $result_as_text);
        }
    }

    public function testSanityCheckIndividualAccountIdOutputtingToScreenAndWithoutNotifyingDiscord(){
        Artisan::call("db:seed", ['--class'=>'UiSampleDatabaseSeeder']);

        DB::statement("TRUNCATE entries");
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

//        Artisan::call($this->_command, ['accountId'=>$account->id, '--notify-screen'=>true, '--dont-notify-discord'=>true]);
        Artisan::call($this->_command, array_merge(['accountId'=>$account->id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertContains(sprintf("Checking account ID:%d\n\tOK", $account->id), $result_as_text);
    }

    public function testSanityCheckIndividualAccountIdNotFoundOutputtingToScreenAndWithoutNotifyingDiscord(){
        Artisan::call("db:seed", ['--class'=>'UiSampleDatabaseSeeder']);
        DB::statement("TRUNCATE accounts");

        $account_id = $this->faker->randomNumber(1);
        Artisan::call($this->_command, array_merge(['accountId'=>$account_id], $this->_screen_only_notification_options));
        $result_as_text = trim(Artisan::output());
        $this->assertContains(sprintf("Account %d not found", $account_id), $result_as_text);
    }

}
