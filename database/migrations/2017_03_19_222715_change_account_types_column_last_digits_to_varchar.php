<?php

use Illuminate\Database\Migrations\Migration;

class ChangeAccountTypesColumnLastDigitsToVarchar extends Migration {

    /**
     * Change the account_types.last_digits column from int to varchar(4)
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE last_digits last_digits VARCHAR(4) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types, we can't modify any columns using ORM
        DB::statement("ALTER TABLE account_types CHANGE last_digits last_digits INT(4) NOT NULL");
    }
}
