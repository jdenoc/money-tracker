<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountType;
use App\Traits\AccountTypeResponseKeys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class AccountTypeController extends Controller {

    use AccountTypeResponseKeys;

    public function list_account_types(){
        $account_types = AccountType::all();
        if(is_null($account_types) || $account_types->isEmpty()){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $account_types = $account_types->toArray();
            $account_types['count'] = AccountType::count();
            return response($account_types, HttpStatus::HTTP_OK);
        }
    }

    public function list_account_type_types(){
        return response(AccountType::getEnumValues(), HttpStatus::HTTP_OK);
    }

    /**
     * GET /api/account-type/{account_type_id}
     */
    public function get_account_type(int $account_type_id){
        $account_type = AccountType::find($account_type_id);
        if(is_null($account_type)){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            return response($account_type, HttpStatus::HTTP_OK);
        }
    }

    /**
     * POST /api/account-type
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create_account_type(Request $request){
        return $this->modify_account_type($request);
    }

    /**
     * PUT /api/account-type/{account_type_id}
     *
     * @param Request $request
     * @param int     $account_type_id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update_account_type(Request $request, int $account_type_id){
        return $this->modify_account_type($request, $account_type_id);
    }

    /**
     * @param Request  $request
     * @param int|null $account_type_id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function modify_account_type(Request $request, int $account_type_id=null){
        $request_body = $request->getContent();
        $account_type_data = json_decode($request_body, true);

        // no data check
        if(empty($account_type_data)){
            return response(
                [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // check validity of account_id value
        if(isset($account_type_data['account_id'])){
            try{
                Account::findOrFail($account_type_data['account_id']);
            }catch (\Exception $exception){
                return response(
                    [self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_INVALID_ACCOUNT, self::$RESPONSE_KEY_ID=>self::$ERROR_ID],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        // check validity of type value
        if(isset($account_type_data['type'])){
            $types = AccountType::getEnumValues();
            if(!in_array($account_type_data['type'], $types)){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_INVALID_TYPE],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        if(is_null($account_type_id)){
            $http_response_status_code = HttpStatus::HTTP_CREATED;
            $account_type_to_modify = new AccountType();
            $required_properties = AccountType::getRequiredFieldsForCreation();

            // missing (required) data check
            $missing_properties = array_diff_key(array_flip($required_properties), $account_type_data);
            if(count($missing_properties) > 0){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>$this->fillMissingPropertyErrorMessage(array_keys($missing_properties))],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        } else {
            $http_response_status_code = HttpStatus::HTTP_OK;
            $required_properties = AccountType::getRequiredFieldsForUpdate();

            // check to make sure account exists. if it doesn't then we can't update it
            $account_type_to_modify = AccountType::find($account_type_id);
            if(is_null($account_type_to_modify)){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
        }

        foreach($account_type_data as $account_type_datum_property=>$account_type_datum_value){
            if(in_array($account_type_datum_property, $required_properties)){
                $account_type_to_modify->{$account_type_datum_property} = $account_type_datum_value;
            }
        }

        // no sense saving if nothing was changed
        if($account_type_to_modify->isDirty()){  // isDirty() === has changes
            $account_type_to_modify->save();
        }
        return response(
            [self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_ERROR, self::$RESPONSE_KEY_ID=>$account_type_to_modify->id],
            $http_response_status_code
        );
    }

    public function disable_account_type($account_type_id){
        $account_type = AccountType::find($account_type_id);
        if(empty($account_type)){
            return response('', HttpStatus::HTTP_NOT_FOUND);
        }

        $account_type->disabled = true;
        $account_type->disabled_stamp = Carbon::now()->toDateTimeString();
        $account_type->save();
        return response('', HttpStatus::HTTP_NO_CONTENT);
    }

}