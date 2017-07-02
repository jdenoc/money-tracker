<?php

namespace App\Http\Controllers\Api;

use App\AccountType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class AccountTypeController extends Controller {

    public function list_account_types(){
        $account_types = AccountType::all();
        if(is_null($account_types) || $account_types->isEmpty()){
            return response([], Response::HTTP_NOT_FOUND);
        } else {
            $account_types->makeHidden('last_updated');
            return response($account_types, Response::HTTP_OK);
        }
    }

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