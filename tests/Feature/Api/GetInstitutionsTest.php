<?php

namespace Tests\Feature\Api;

class GetInstitutionsTest extends ListInstitutionsBase {

    public function setUp(){
        parent::setUp();
        $this->_base_uri = '/api/institutions';
    }

    public function testGetInstitutionsWhenNotAvailable(){
        $this->getInstitutionsWhenNoneAreAvailableTest();
    }

    /**
     * @dataProvider providerGetInstitutions
     * @param boolean $all_institutions
     */
    public function testGetInstitutions($all_institutions){
        $this->getInstitutionsTest($all_institutions);
    }

}