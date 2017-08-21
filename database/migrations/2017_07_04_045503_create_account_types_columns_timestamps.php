<?php

use Illuminate\Database\Migrations\Migration;

class CreateAccountTypesColumnsTimestamps extends Migration {

    /**
     * Add column account_types.create_stamp
     * Renamed account_types.last_updated to account_types.modified_stamp
     * Add column account_types.disabled_stamp
     *
     * @return void
     */
    public function up(){
        // Because there is a column of type ENUM in the account_types table, we can't modify any columns using ORM
        DB::statement("ALTER TABLE `account_types` ADD `create_stamp` TIMESTAMP NULL DEFAULT NULL AFTER `disabled`");
        DB::statement("ALTER TABLE `account_types` CHANGE `last_updated` `modified_stamp` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        DB::statement("ALTER TABLE `account_types` ADD `disabled_stamp` TIMESTAMP NULL DEFAULT NULL AFTER `modified_stamp`");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // Because there is a column of type ENUM in the account_types table, we can't modify any columns using ORM
        DB::statement("ALTER TABLE `account_types` DROP `create_stamp`");
        DB::statement("ALTER TABLE `account_types` DROP `disabled_stamp`");
        DB::statement("ALTER TABLE `account_types` CHANGE `modified_stamp` `last_updated` TIMESTAMP on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

}