<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsColumnDisabled extends Migration {

    /**
     * Add column accounts.disabled
     *
     * @return void
     */
    public function up(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->unsignedTinyInteger('disabled')->after('institution_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('disabled');
        });
    }

}