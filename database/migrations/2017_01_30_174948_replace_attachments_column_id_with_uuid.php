<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReplaceAttachmentsColumnIdWithUuid extends Migration {

    /**
     * Re-create attachments table
     *
     * @return void
     */
    public function up(){
        // Create new_attachments table
        Schema::create('new_attachments', function (Blueprint $table) {
            $table->char('uuid', 36);
            $table->unsignedInteger('entry_id')->index();
            $table->string('attachment');
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->primary('uuid');
        });

        // migrate data from attachments to new_attachments
        DB::statement("INSERT INTO new_attachments (SELECT uid, entry_id, attachment, stamp FROM attachments)");

        // drop attachments table
        Schema::drop('attachments');

        // rename new_attachments to attachments
        Schema::rename('new_attachments', 'attachments');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        // create original_attachments table
        Schema::create('original_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entry_id')->index();
            $table->string('attachment');
            $table->string('uid', 50)->nullable()->unique();
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        // migrate data from attachments to original_attachments
        DB::statement("INSERT INTO original_attachments (SELECT null, entry_id, attachment, uuid, stamp FROM attachments)");

        // drop attachments table
        Schema::drop('attachments');

        // rename original_attachments to attachments
        Schema::rename('original_attachments', 'attachments');
    }

}