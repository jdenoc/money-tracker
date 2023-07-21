<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexToEntryTagsPivotTable extends Migration {

    private static $INDEX_NAME = "entry_tag_pivot_index";
    private static $TABLE_NAME = 'entry_tags';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->index(['entry_id', 'tag_id'], self::$INDEX_NAME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE_NAME, function(Blueprint $table) {
            $table->dropIndex(self::$INDEX_NAME);
        });
    }

}
