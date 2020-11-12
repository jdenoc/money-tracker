<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeEntryTagsPrimaryKeyToCompoundPrimaryKey extends Migration {

    private static $TABLE = 'entry_tags';
    private static $OLD_INDEX_NAME = "entry_tag_pivot_index";   // comes from 2018_02_12_192742_add_index_to_entry_tags_pivot_table.php
    private static $PRIMARY_INDEX_COLUMNS = ['entry_id', 'tag_id'];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            /**
             * QUERY from below code:
                delete from entry_tags
                where id in (
                  select id from (
                    select id from entry_tags
                    group by entry_id, tag_id
                      having count(id) > 1
                  )
                );
             */
            $duplicate_ids = DB::table(self::$TABLE)
                ->select('id')
                ->groupBy(self::$PRIMARY_INDEX_COLUMNS)
                ->havingRaw('count(id) > 1')
                ->get()->pluck('id');
            DB::table(self::$TABLE)
                ->whereIn('id', $duplicate_ids)
                ->delete();

            $table->dropIndex(self::$OLD_INDEX_NAME);
            $table->dropColumn('id');
            $table->primary(self::$PRIMARY_INDEX_COLUMNS);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->dropPrimary();
            $table->index(self::$PRIMARY_INDEX_COLUMNS, self::$OLD_INDEX_NAME);
        });

        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->increments('id');
        });

        Schema::table(self::$TABLE, function (){
            // There is no nice way to move columns around using laravel/blueprint code
            DB::statement("ALTER TABLE ".self::$TABLE." MODIFY COLUMN stamp timestamp not null AFTER id");
            DB::statement("ALTER TABLE ".self::$TABLE." MODIFY COLUMN tag_id int unsigned not null AFTER id");
            DB::statement("ALTER TABLE ".self::$TABLE." MODIFY COLUMN entry_id int unsigned not null AFTER id");
        });
    }
}
