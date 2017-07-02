<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameEntriesColumnDate extends Migration {

    /**
     * Rename column entries.date to entries.entry_date
     *
     * @return void
     */
    public function up(){
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(array('date'));
            $table->renameColumn('date', 'entry_date');
            $table->index('entry_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex(array('entry_date'));
            $table->renameColumn('entry_date', 'date');
            $table->index('date');
        });
    }

}