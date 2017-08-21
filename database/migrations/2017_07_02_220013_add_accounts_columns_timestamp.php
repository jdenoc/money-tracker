<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnsTimestamp extends Migration {

    /**
     * Add column accounts.create_stamp
     * Add column accounts.modified_stamp
     * Add column accounts.disabled_stamp
     *
     * @return void
     */
    public function up(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->timestamp('create_stamp')->nullable();
            $table->timestamp('modified_stamp')->useCurrent();
            $table->timestamp('disabled_stamp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('create_stamp');
            $table->dropColumn('modified_stamp');
            $table->dropColumn('disabled_stamp');
        });
    }

}