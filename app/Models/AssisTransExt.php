<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisTransExt extends Model {

    protected $table = 'registro_casos_transporte_extension';

    protected $fillable = [
        'id', 'fk_registro_casos_transporte', 'fecha_extension', 'fecha_salida', 'nro_pr', 'grupo'
    ];
}