<?php

namespace App\Http\Controllers\Api;

use App\AccountType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class AccountTypeController extends Controller {

    public function disable_account_type($account_type_id){
        $account_type = AccountType::find($account_type_id);
        if(empty($account_type)){
            return response('', Response::HTTP_NOT_FOUND);
        }

        $account_type->disabled = true;
        $account_type->save();
        return response('', Response::HTTP_NO_CONTENT);
    }

}