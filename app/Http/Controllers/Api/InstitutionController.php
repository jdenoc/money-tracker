<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Traits\InstitutionResponseKeys;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class InstitutionController extends Controller {

    use InstitutionResponseKeys;

    /**
     * GET /api/institutions
     * @return Response
     */
    public function get_institutions():Response{
        $institutions = Institution::all();
        if(is_null($institutions) || $institutions->isEmpty()){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $institutions->makeHidden(['create_stamp', 'modified_stamp']);
            $institutions = $institutions->toArray();
            $institutions['count'] = Institution::count();
            return response($institutions, HttpStatus::HTTP_OK);
        }
    }

    /**
     * @param int $institution_id
     * @return Response
     */
    public function get_institution(int $institution_id):Response{
        $institution = Institution::find_institution_with_accounts($institution_id);
        if(is_null($institution)){
            return response([], HttpStatus::HTTP_NOT_FOUND);
        } else {
            $institution->accounts->makeHidden([
                'institution_id',    // We already know what account this is. We don't need to re-show it.
                'create_stamp',
                'modified_stamp',
                'disabled_stamp'
            ]);
            return response($institution, HttpStatus::HTTP_OK);
        }
    }

    public function create_institution(Request $request):Response{
        return $this->modify_institution($request);
    }

    public function update_institution(Request $request, int $institutionId):Response{
        return $this->modify_institution($request, $institutionId);
    }

    private function modify_institution(Request $request, int $institutionId=null):Response{
        $request_body = $request->getContent();
        $institution_data = json_decode($request_body, true);

        // no data check
        if(empty($institution_data)){
            return response(
                [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_DATA],
                HttpStatus::HTTP_BAD_REQUEST
            );
        }

        if(is_null($institutionId)){
            $http_response_status_code = HttpStatus::HTTP_CREATED;
            $required_fields = Institution::getRequiredFieldsForCreation();
            $institution_to_modify = new Institution();

            // missing (required) data check
            $missing_properties = array_diff_key(array_flip($required_fields), $institution_data);
            if(count($missing_properties) > 0){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>$this->fillMissingPropertyErrorMessage(array_keys($missing_properties))],
                    HttpStatus::HTTP_BAD_REQUEST
                );
            }
        } else {
            $http_response_status_code = HttpStatus::HTTP_OK;
            $required_fields = Institution::getRequiredFieldsForUpdate();
            try{
                $institution_to_modify = Institution::findOrFail($institutionId);
            } catch(Exception $exception){
                return response(
                    [self::$RESPONSE_KEY_ID=>self::$ERROR_ID, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_DOES_NOT_EXIST],
                    HttpStatus::HTTP_NOT_FOUND
                );
            }
        }

        foreach($institution_data as $property=>$value){
            if(in_array($property, $required_fields)){
                $institution_to_modify->$property = $value;
            }
        }

        // no sense saving if nothing was changed
        if($institution_to_modify->isDirty()){    // isDirty() == has changes
            $institution_to_modify->save();
        }

        return response(
            [self::$RESPONSE_KEY_ID=>$institution_to_modify->id, self::$RESPONSE_KEY_ERROR=>self::$ERROR_MSG_NO_ERROR],
            $http_response_status_code
        );
    }

}