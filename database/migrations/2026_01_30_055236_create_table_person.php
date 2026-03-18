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
        Schema::create('person', function (Blueprint $table) {
            $table->increments('person_id');
            $table->char('secret', length: 8)->collation('utf8mb4_0900_as_cs')->unique()->default('00000000')->comment('Код (пароль) входа');
            $table->string('f', length: 30)->comment('Фамилия');
            $table->string('i', length: 30)->comment('Имя');
            $table->string('o', length: 30)->nullable()->comment('Отчество');
            $table->unsignedSmallInteger('org_id')->nullable()->comment('ID организации работника');
            $table->unsignedTinyInteger('level')->default(10)->comment('Уровень доступа: 0-Админ; 1-Менеджер; 10-Обычные');
            $table->boolean('enabled')->default(true)->comment('Включено');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person');
    }
};
