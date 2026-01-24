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
            $table->unsignedTinyInteger('day_of_week')
                ->nullable()
                ->comment('deprecated: use date instead')
                ->change();
        });
    }


    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->unsignedTinyInteger('day_of_week')
                ->nullable(false)
                ->change();
        });
    }

};
