<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntriesColumnDisabledStamp extends Migration {

    /**
     * Add column entries.disabled_stamp
     * Assign a value to entries.disabled_stamp if entries.disabled == 1
     *
     * @return void
     */
    public function up(){
        Schema::table('entries', function (Blueprint $table) {
            $table->timestamp('disabled_stamp')->nullable();
        });
        DB::statement("UPDATE entries SET disabled_stamp=modified_stamp WHERE disabled=1");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entries', function (Blueprint $table) {
            $table->dropColumn('disabled_stamp');
        });
    }

}