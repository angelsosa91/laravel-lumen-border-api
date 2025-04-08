<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisHostExt extends Model {

    protected $table = 'registro_casos_alojamiento_extension';

    protected $fillable = [
        'id', 'fk_registro_casos_alojamiento', 'fecha_extension', 'fecha_salida', 'nro_pr', 'grupo'
    ];
}