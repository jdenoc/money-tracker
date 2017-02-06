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

}
