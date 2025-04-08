<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Potential extends Model {

    protected $table = 'data_asistencia_potencial';

    protected $fillable = ['id', 'descripcion', 'estado'];
}
