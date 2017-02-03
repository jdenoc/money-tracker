<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Account;

class AccountController extends Controller {

    /**
     * GET /api/accounts
     */
    public function get_accounts(){
        $accounts = Account::all();
        $accounts = $accounts->toArray();
        $accounts['count'] = Account::count();

        return response($accounts);
    }

}
