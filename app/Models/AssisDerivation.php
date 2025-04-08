<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisDerivation extends Model {

    protected $table = 'registro_casos_derivacion';

    protected $fillable = [
        'id', 'fk_registro_casos_asistencia', 'institucion', 'motivo' 
    ];
}