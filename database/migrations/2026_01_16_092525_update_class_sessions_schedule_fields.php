<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at']);

            $table->unsignedTinyInteger('day_of_week'); // 1~7
            $table->time('start_time');                 // 18:00
            $table->unsignedSmallInteger('duration_min'); // 60, 90
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropColumn(['day_of_week', 'start_time', 'duration_min']);

            $table->dateTime('start_at');
            $table->dateTime('end_at');
        });
    }

};
