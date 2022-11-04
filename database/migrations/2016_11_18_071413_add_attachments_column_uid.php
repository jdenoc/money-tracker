<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentsColumnUid extends Migration {

    private static $TABLE = 'attachments';
    private static $COLUMN_NEW = 'uid';

    /**
     * Add column attachments.uid
     *
     * @return void
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->string(self::$COLUMN_NEW, 50)->after('attachment')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN_NEW);
        });
    }

}
