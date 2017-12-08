<?php

namespace Tests;

use App\Account;
use App\AccountType;
use App\Attachment;
use App\Entry;
use App\Institution;
use App\Tag;
use App\User;

trait InjectDatabaseStateIntoException {

    public static $ALLOW_INJECT_DATABASE_STATE_ON_EXCEPTION = true;
    public static $DENY_INJECT_DATABASE_STATE_ON_EXCEPTION = false;

    private $can_inject_database_state = false;

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
     * @param \Exception $exception
     * @param string $injectable_message
     * @return \Exception
     */
    public function injectMessageIntoException($exception, $injectable_message){
        if($this->isDatabaseStateInjectionAllowed()){
            $exception_message = $exception->getMessage()."\n".$injectable_message;
            $exception_name = get_class($exception);
            return new $exception_name($exception_message);
        } else {
            return $exception;
        }
    }


}