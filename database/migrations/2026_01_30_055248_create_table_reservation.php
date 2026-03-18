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
        Schema::create('reservation', function (Blueprint $table) {
            $table->increments('reservation_id');
            $table->date('reserv_date')->comment('Дата планируемого посещения');
            $table->unsignedInteger('schedule_id')->comment('ID расписания');
            $table->unsignedInteger('person_id')->comment('ID сотрудника');
            $table->timestamps();
            $table->foreign('person_id', 'reservation~person~fk')->references('person_id')->on('reservation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation');
    }
};
