<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Account;

class AccountController extends Controller {

    /**
     * GET /api/accounts
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_accounts(){
        $accounts = Account::all();
        if(is_null($accounts) || $accounts->isEmpty()){
            return response([], 404);
        } else {
            $accounts = $accounts->toArray();
            $accounts['count'] = Account::count();

            return response($accounts);
        }
    }

    /**
     * GET /api/account/{account_id}
     * @param int $account_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_account($account_id){
        $account = Account::find_account_with_types($account_id);
        if(is_null($account)){
            return response([], 404);
        } else {
            $account_as_array = $account->toArray();
            foreach($account_as_array['account_types'] as $key=>$account_type){
                unset($account_as_array['account_types'][$key]['account_group']);  // We already know what account this is. We don't need to re-show it.
                unset($account_as_array['account_types'][$key]['disabled']);       // We're only showing non-disabled account_types. No need to show that they're actually disabled.
                unset($account_as_array['account_types'][$key]['last_updated']);   // We're
            }
            return response($account_as_array);
        }
    }

}
