<?php

namespace App\Traits\Tests;

use App\Account;
use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Institution;
use App\Tag;
use App\User;

/**
 * @deprecated - deprecating in favor of DatabaseFileDump
 */
trait InjectDatabaseStateIntoException {

    public static $ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION = true;
    public static $DENY_INJECT_DATABASE_STATE_ON_EXCEPTION = false;

    /**
     * @var bool
     */
    private $can_inject_database_state = false;
    /**
     * @var string
     */
    private $_database_state = '';

    /**
     * @return bool
     */
    public function isDatabaseStateInjectionAllowed(){
        return $this->can_inject_database_state;
    }

    /**
     * @param bool $can_inject_database_state
     */
    public function setDatabaseStateInjectionPermission($can_inject_database_state){
        if($can_inject_database_state !== self::$ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION && $can_inject_database_state !== self::$DENY_INJECT_DATABASE_STATE_ON_EXCEPTION){
            throw new \UnexpectedValueException("Attempted to set a value other than those permitted when setting database state injection permission");
        }
        $this->can_inject_database_state = $can_inject_database_state;
    }

    /**
     * @return string
     */
    protected function getDatabaseState(){
        $database_collection = collect();
        $database_collection->put('tags', Tag::all());
        $database_collection->put('users', User::all());
        $database_collection->put('institutions', Institution::all());
        $database_collection->put('accounts', Account::all());
        $database_collection->put('account_types', AccountType::all());
        $database_collection->put('entries+entry_tags', Entry::with('tags')->get());   // get a collection of entries with their tags
        $database_collection->put('attachments', Attachment::all());
        return $database_collection->toJson();
    }

    /**
     * @param \Exception $original_exception
     * @param string $injectable_message
     * @return \Exception
     */
    public function injectMessageIntoException($original_exception, $injectable_message){
        if($this->isDatabaseStateInjectionAllowed()){
            $new_exception_message = $original_exception->getMessage()."\n\n".$injectable_message;

            $exception_name = get_class($original_exception);

            switch($exception_name){
                case \Illuminate\Database\QueryException::class:
                    return new $exception_name($new_exception_message, $original_exception->getBindings(), $original_exception);

                case \SebastianBergmann\Comparator\ComparisonFailure::class:
                    return new  $exception_name($original_exception->getExpected(), $original_exception->getActual(), $original_exception->getExpectedAsString(), $original_exception->getActualAsString(), false, $new_exception_message);

                case \PHPUnit\Framework\ExpectationFailedException::class:
                    return new $exception_name($new_exception_message, $original_exception->getComparisonFailure(), $original_exception);

                case \ErrorException::class:
                    return new $exception_name($new_exception_message, $original_exception->getCode(), $original_exception->getSeverity(), $original_exception->getFile(), $original_exception->getLine(), $original_exception);

                default:
                    return new $exception_name($new_exception_message, $original_exception->getCode(), $original_exception);
            }
        } else {
            return $original_exception;
        }
    }

    /**
     * @param \Throwable|\Exception $unsuccessful_test_exception
     * @throws \Exception
     * @throws \Throwable
     */
    protected function onNotSuccessfulTest($unsuccessful_test_exception){
        $exception_message_to_inject = "Database state on failure:\n".$this->_database_state;
        $unsuccessful_test_exception = $this->injectMessageIntoException($unsuccessful_test_exception, $exception_message_to_inject);

        parent::onNotSuccessfulTest($unsuccessful_test_exception); // this needs to occur at the end of the method, or things won't get output.
    }

    public function prepareFailureExceptionForDatabaseInjection(){
        $this->beforeApplicationDestroyed(function(){
            if($this->isDatabaseStateInjectionAllowed()){
                // database truncation occurs in the tearDown() step, before we reach onNotSuccessfulTest()
                $this->_database_state = $this->getDatabaseState();
            }
        });
    }

}