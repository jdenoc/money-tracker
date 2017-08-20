<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnDeletedToDisabled extends Migration {

    /**
     * Rename column entries.deleted to entries.disabled
     *
     * @return void
     */
    public function up(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('deleted', 'disabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('disabled', 'deleted');
        });
    }

}