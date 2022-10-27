<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScheduleMonitorTables extends Migration {

    private static string $TABLE_TASKS = 'monitored_scheduled_tasks';
    private static string $TABLE_LOGS = 'monitored_scheduled_task_log_items';

    public function up(){
        Schema::create(self::$TABLE_TASKS, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('name');
            $table->string('type')->nullable();
            $table->string('cron_expression');
            $table->string('timezone')->nullable();
            $table->string('ping_url')->nullable();

            $table->dateTime('last_started_at')->nullable();
            $table->dateTime('last_finished_at')->nullable();
            $table->dateTime('last_failed_at')->nullable();
            $table->dateTime('last_skipped_at')->nullable();

            $table->dateTime('registered_on_oh_dear_at')->nullable();
            $table->dateTime('last_pinged_at')->nullable();
            $table->integer('grace_time_in_minutes');

            $table->timestamps();
        });

        Schema::create(self::$TABLE_LOGS, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('monitored_scheduled_task_id');
            $table
                ->foreign('monitored_scheduled_task_id', 'fk_scheduled_task_id')
                ->references('id')
                ->on('monitored_scheduled_tasks')
                ->cascadeOnDelete();

            $table->string('type');

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists(self::$TABLE_LOGS);
        Schema::dropIfExists(self::$TABLE_TASKS);
    }

}
