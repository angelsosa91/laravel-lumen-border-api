<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Family extends Model {

    protected $table = 'registro_casos_asistencia_familia';

    protected $fillable = [
        'id', 'id_registro_asistencia', 'nombres', 'documento', 'nacimiento', 'salud', 'genero', 'observaciones'
    ];
}
