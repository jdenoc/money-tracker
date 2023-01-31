<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\Institution;
use App\Traits\AccountResponseKeys;
use Brick\Money\ISOCurrencyProvider;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class AccountController extends Controller {
    use AccountResponseKeys;

    /**
     * GET /api/accounts
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_accounts() {
        $accounts = Account::cache()->get('all');
        if (is_null($accounts) || $accounts->isEmpty()) {
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $accounts->makeHidden([
                'create_stamp',
                'modified_stamp',
                'disabled_stamp'
            ]);
            $accounts = $accounts->toArray();
            $accounts['count'] = Account::cache()->get('count');

            return response($accounts, HttpStatus::HTTP_OK);
        }
    }

    /**
     * GET /api/account/{account_id}
     * @param int $account_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function get_account(int $account_id) {
        $account = Account::find_account_with_types($account_id);
        if (is_null($account)) {
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $account->account_types->makeHidden([
                'account_id',    // We already know what account this is. We don't need to re-show it.
                AccountType::CREATED_AT,
                AccountType::UPDATED_AT,
                'disabled_stamp',
            ]);
            return response($account, HttpStatus::HTTP_OK);
        }
    }

    /**
     * POST /api/account
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function create_account(Request $request) {
        return $this->modify_account($request);
    }

    /**
     * PUT /api/account/{account_id}
     * @param Request $request
     * @param int $account_id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function update_account(Request $request, int $account_id) {
        return $this->modify_account($request, $account_id);
    }

    public function modify_account(Request $request, int $account_id=null) {
        $request_body = $request->getContent();
        $account_data = json_decode($request_body, true);

        // no data check
        if (empty($account_data)) {
            return response(
                [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        // check validity of institution_id value
        if (isset($account_data['institution_id'])) {
            $institution = Institution::find($account_data['institution_id']);
            if (empty($institution)) {
                return response(
                    [self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_INVALID_INSTITUTION, self::$RESPONSE_KEY_ID=>self::$ERROR_ID],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        }

        if (isset($account_data['currency']) && !in_array($account_data['currency'], ISOCurrencyProvider::getInstance()->getAvailableCurrencies())) {
            return response(
                [self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_INVALID_CURRENCY, self::$RESPONSE_KEY_ID=>self::$ERROR_ID],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        if (is_null($account_id)) {
            $account_to_modify = new Account();
            $required_properties = Account::getRequiredFieldsForCreation();
            $http_response_status_code = HttpStatus::HTTP_CREATED;

            // missing (required) data check
            $missing_properties = array_diff_key(array_flip($required_properties), $account_data);
            if (count($missing_properties) > 0) {
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>$this->fillMissingPropertyErrorMessage(array_keys($missing_properties))],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        } else {
            $required_properties = Account::getRequiredFieldsForUpdate();
            $http_response_status_code = HttpStatus::HTTP_OK;

            try {
                // check to make sure account exists. if it doesn't then we can't update it
                $account_to_modify = Account::findOrFail($account_id);
            } catch(\Exception $exception) {
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
        }

        ksort($account_data);   // currency needs to be set prior to entry_value
        foreach ($account_data as $account_datum_property=>$account_datum_value) {
            if (in_array($account_datum_property, $required_properties)) {
                $account_to_modify->{$account_datum_property} = $account_datum_value;
            }
        }

        // no sense saving if nothing was changed
        if ($account_to_modify->isDirty()) {  // isDirty() === has changes
            $account_to_modify->save();
        }
        return response(
            [self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_ERROR, self::$RESPONSE_KEY_ID=>$account_to_modify->id],
            $http_response_status_code
        );
    }

}
