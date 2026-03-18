<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    /**
     * Таблица БД, ассоциированная с моделью.
     *
     * @var string
     */
    protected $table = 'person';
    protected $primaryKey = 'person_id';
    protected $fillable = ['secret', 'f', 'i', 'o', 'org_id'];
}
