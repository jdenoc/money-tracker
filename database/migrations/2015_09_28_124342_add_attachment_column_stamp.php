<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentColumnStamp extends Migration {

    /**
     * Add column attachments.stamp
     *
     * @return void
     */
    public function up(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->timestamp('stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropColumn('stamp');
        });
    }

}