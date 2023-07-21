<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUuidColumnToFailedJobsAfterId extends Migration {

    private static $TABLE = 'failed_jobs';
    private static $COLUMN = 'uuid';

    /**
     * Run the migrations.
     */
    public function up() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->string(self::$COLUMN)->after('id')->nullable()->unique();
        });

        // generate UUIDs for your existing failed jobs
        DB::table(self::$TABLE)->whereNull(self::$COLUMN)->cursor()->each(function($job) {
            DB::table(self::$TABLE)
                ->where('id', $job->id)
                ->update([self::$COLUMN => (string) Illuminate\Support\Str::uuid()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down() {
        Schema::table(self::$TABLE, function(Blueprint $table) {
            $table->dropColumn(self::$COLUMN);
        });
    }

}
