<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameAttachmentsAttachmentColumnToName extends Migration {

    /**
     * Rename attachments.attachment to attachments.name
     *
     * @return void
     */
    public function up(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->renameColumn('attachment', 'name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->renameColumn('name', 'attachment');
        });
    }

}