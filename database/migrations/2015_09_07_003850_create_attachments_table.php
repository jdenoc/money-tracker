<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration {

    private static $TABLE = 'attachments';

    /**
     * Create `attachments` table
     *
     * @return void
     */
    public function up() {
        Schema::create(self::$TABLE, function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('entry_id')->index();
            $table->string('attachment');
            $table->string('ext', 10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists(self::$TABLE);
    }

}
