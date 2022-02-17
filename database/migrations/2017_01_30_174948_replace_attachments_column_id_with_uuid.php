<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReplaceAttachmentsColumnIdWithUuid extends Migration {

    private static $TABLE = 'attachments';
    private static $TABLE_REPLACEMENT = 'replace_attachments';

    /**
     * Re-create attachments table
     *
     * @return void
     */
    public function up(){
        // Create replacement_attachments table
        Schema::create(self::$TABLE_REPLACEMENT, function (Blueprint $table) {
            $table->char('uuid', 36);
            $table->unsignedInteger('entry_id')->index();
            $table->string('attachment');
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->primary('uuid');
        });

        // migrate data from attachments to replacement_attachments
        DB::statement("INSERT INTO ".self::$TABLE_REPLACEMENT." (SELECT uid, entry_id, attachment, stamp FROM ".self::$TABLE.")");

        // drop attachments table
        Schema::drop(self::$TABLE);

        // rename replacement_attachments to attachments
        Schema::rename(self::$TABLE_REPLACEMENT, self::$TABLE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // create replacement_attachments table
        Schema::create(self::$TABLE_REPLACEMENT, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entry_id')->index();
            $table->string('attachment');
            $table->string('uid', 50)->nullable()->unique();
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // migrate data from attachments to replacement_attachments
        DB::statement("INSERT INTO ".self::$TABLE_REPLACEMENT." (SELECT null, entry_id, attachment, uuid, stamp FROM ".self::$TABLE.")");

        // drop attachments table
        Schema::drop(self::$TABLE);

        // rename replacement_attachments to attachments
        Schema::rename(self::$TABLE_REPLACEMENT, self::$TABLE);
    }

}