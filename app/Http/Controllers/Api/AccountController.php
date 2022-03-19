<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends Controller {

    /**
     * GET /api/accounts
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_accounts(){
        $accounts = Account::all();
        if(is_null($accounts) || $accounts->isEmpty()){
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            $accounts->makeHidden([
                'create_stamp',
                'modified_stamp',
                'disabled_stamp'
            ]);
            $accounts = $accounts->toArray();
            $accounts['count'] = Account::count();

            return response($accounts, Response::HTTP_OK);
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
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            $account->account_types->makeHidden([
                'account_id',    // We already know what account this is. We don't need to re-show it.
            ]);
            return response($account, Response::HTTP_OK);
        }
    }

}