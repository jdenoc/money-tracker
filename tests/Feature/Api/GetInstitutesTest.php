<?php

namespace Tests\Feature\Api;

class GetInstitutesTest extends ListInstitutionsBase {

    public function setUp(){
        parent::setUp();
        $this->_base_uri = '/api/institutes';
    }

    public function testGetInstitutesWhenNotAvailable(){
        $this->getInstitutionsWhenNoneAreAvailableTest();
    }

    /**
     * @dataProvider providerGetInstitutions
     * @param boolean $all_institutes
     */
    public function testGetInstitutes($all_institutes){
        $this->getInstitutionsTest($all_institutes);
    }

}