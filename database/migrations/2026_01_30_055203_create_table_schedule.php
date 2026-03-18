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
        Schema::create('schedule', function (Blueprint $table) {
            $table->increments('schedule_id');
            $table->unsignedTinyInteger('day_of_week')->comment('День недели');
            $table->unsignedTinyInteger('hour_start')->comment('Час начала');
            $table->string('schedule_name', length: 20)->comment('Наименование времени (напр. 09:00 - 10:00');
            $table->boolean('enabled')->default(false)->comment('Включено');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
