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
        Schema::create('visit', function (Blueprint $table) {
            $table->increments('visit_id');
            $table->date('visit_date')->comment('Дата фактического посещения');
            $table->unsignedInteger('schedule_id')->comment('ID расписания');
            $table->unsignedInteger('person_id')->comment('ID сотрудника');
            $table->timestamps();
            $table->foreign('person_id', 'visit~person~fk')->references('person_id')->on('person');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit');
    }
};
