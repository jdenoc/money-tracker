<?php

namespace App\Console\Commands;

use App\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountTotalSanityCheck extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanity-check:account-total';

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
        $accounts = Account::all();
        foreach($accounts as $account){
            $sanity_check_query = DB::table('entries')
                ->select(DB::raw("IFNULL( SUM( IF( entries.expense=1, -1*entries.entry_value, entries.entry_value ) ), 0 ) as actual"))
                ->join('account_types', function($join) use ($account){
                    $join->on('entries.account_type_id', '=', 'account_types.id')
                        ->where('account_types.account_id', $account->id);
                })
                ->where('entries.disabled', '0')
                ->orderBy(DB::raw('entries.entry_date desc, entries.id'), 'desc');
            // The above stuff is translated into MySQL here:
            // SELECT
            //   @ACCOUNT_TOTAL as expected,
            //   IFNULL( SUM( IF( entries.expense=1, -1*entries.entry_value, entries.entry_value ) ), 0 ) as actual
            // FROM entries
            // INNER JOIN account_types
            //   ON entries.account_type_id = account_types.id
            //   AND account_types.account_id = @ACCOUNT_ID
            // WHERE entries.disabled = 0
            // ORDER BY entries.entry_date DESC, entries.id DESC

            Log::debug($sanity_check_query->toSql());
            $sanity_check = $sanity_check_query->get();
            Log::debug($sanity_check);
            if($account->total != $sanity_check[0]->actual){
                $sanity_check->put('expected', $account->total);
                $sanity_check->put('diff', $sanity_check[0]->actual - $account->total);
                $sanity_check->put('account ID', $account->id);
                $sanity_check->put('account name', $account->name);
                Log::emergency("Sanity check has failed ".$sanity_check);
                // TODO: logging to emergency log file is a great start, just need the user to know too
            }
        }
    }
}
