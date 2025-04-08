<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisFiles extends Model {

    protected $table = 'registro_casos_asistencia_archivos';

    protected $fillable = [
        'id', 'fk_registro_casos_asistencia', 'descripcion', 'path', 'extension'
    ];
}