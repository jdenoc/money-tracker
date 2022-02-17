<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAttachmentsAttachmentColumnToName extends Migration {

    private static $TABLE = 'attachments';
    private static $COLUMN_OLD_NAME = 'attachment';
    private static $COLUMN_NEW_NAME = 'name';

    /**
     * Rename attachments.attachment to attachments.name
     *
     * @return void
     */
    public function up(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->renameColumn(self::$COLUMN_OLD_NAME, self::$COLUMN_NEW_NAME);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table(self::$TABLE, function (Blueprint $table) {
            $table->renameColumn(self::$COLUMN_NEW_NAME, self::$COLUMN_OLD_NAME);
        });
    }

}