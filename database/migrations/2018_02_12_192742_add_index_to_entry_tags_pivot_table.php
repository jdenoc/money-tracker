<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToEntryTagsPivotTable extends Migration {

    const INDEX_NAME = "entry_tag_pivot_index";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table('entry_tags', function (Blueprint $table) {
            $table->index(['entry_id', 'tag_id'], self::INDEX_NAME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('entry_tags', function (Blueprint $table) {
            $table->dropIndex(self::INDEX_NAME);
        });
    }

}