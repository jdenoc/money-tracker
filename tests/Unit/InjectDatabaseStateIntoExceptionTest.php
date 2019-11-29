<?php
/**
 * Created by PhpStorm.
 * User: Denis
 * Date: 2017-12-07
 * Time: 09:41
 */

namespace Tests\Unit;

use Tests\TestCase;

class InjectDatabaseStateIntoExceptionTest extends TestCase {

    // NOTE: InjectDatabaseStateIntoException trait has already been included by the TestCase class

    public function testSettingValidInjectionPermission(){
        $current_state = $this->isDatabaseStateInjectionAllowed();
        $this->assertEquals(self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION, $current_state, "by default, we should NOT be able to inject a database state into an exception");

        $this->setDatabaseStateInjectionPermission(self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION);
        $current_state = $this->isDatabaseStateInjectionAllowed();
        $this->assertEquals(self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION, $current_state);

        $this->setDatabaseStateInjectionPermission(self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION);
        $current_state = $this->isDatabaseStateInjectionAllowed();
        $this->assertEquals(self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION, $current_state);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testSettingInvalidInjectionPermission(){
        $current_state = $this->isDatabaseStateInjectionAllowed();
        $this->assertEquals(self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION, $current_state, "by default, we should NOT be able to inject a database state into an exception");

        $this->setDatabaseStateInjectionPermission("this_should_cause_an_exception");
    }

    public function testInjectMessageIntoException(){
        $this->setDatabaseStateInjectionPermission(self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION);
        $injected_exception_message = 'This text is injected into the exception';
        try{
            throw new \Exception("this is the default exception message");
        } catch(\Exception $e){
            $exception_with_injected_message = $this->injectMessageIntoException($e, $injected_exception_message);
            $this->assertContains($injected_exception_message, $exception_with_injected_message->getMessage());
        }
    }

    public function testInjectMessageIntoExceptionWithPermissionDenied(){
        $this->setDatabaseStateInjectionPermission(self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION);
        $injected_exception_message = 'This text is NOT injected into the exception';
        try{
            throw new \Exception("this is the default exception message");
        } catch(\Exception $e){
            $exception_with_injected_message = $this->injectMessageIntoException($e, $injected_exception_message);
            $this->assertNotContains($injected_exception_message, $exception_with_injected_message->getMessage());
        }
    }

    public function testGetDatabaseState(){
        $database_state_as_json = $this->getDatabaseState();
        $this->assertJson($database_state_as_json);
        $database_state = json_decode($database_state_as_json, true);
        $database_state = $this->assertDatabaseStateElement('tags', $database_state);
        $database_state = $this->assertDatabaseStateElement('users', $database_state);
        $database_state = $this->assertDatabaseStateElement('institutions', $database_state);
        $database_state = $this->assertDatabaseStateElement('accounts', $database_state);
        $database_state = $this->assertDatabaseStateElement('account_types', $database_state);
        $database_state = $this->assertDatabaseStateElement('entries+entry_tags', $database_state);
        $database_state = $this->assertDatabaseStateElement('attachments', $database_state);
        $this->assertEmpty($database_state);
    }

    /**
     * @param string $element_name
     * @param array $database_state
     * @return array
     */
    private function assertDatabaseStateElement($element_name, $database_state){
        $this->assertArrayHasKey($element_name, $database_state);
        $this->assertTrue(is_array($database_state[$element_name]));
        unset($database_state[$element_name]);
        return $database_state;
    }

}

