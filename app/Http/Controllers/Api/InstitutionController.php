<?php

namespace App\Http\Controllers\Api;

use App\Institution;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpStatus;

class InstitutionController extends Controller {

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

}