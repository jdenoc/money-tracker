<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstitutionsTable extends Migration {



    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        Schema::create('institutions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->tinyInteger('active')->default(1);
            $table->timestamp('create_stamp')->nullable();
            $table->timestamp('modified_stamp')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        Schema::dropIfExists('institutions');
    }

}