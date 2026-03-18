<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shedule extends Model
{
    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'schedule';
    protected $primaryKey = 'schedule_id';
}
