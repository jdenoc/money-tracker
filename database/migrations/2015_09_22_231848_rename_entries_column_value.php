<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnValue extends Migration {

    /**
     * Rename column entries.value to entries.entry_value
     *
     * @return void
     */
    public function up(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('value', 'entry_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('entry_value', 'value');
        });
    }

}