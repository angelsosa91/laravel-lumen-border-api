<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisTrans extends Model {

    protected $table = 'registro_casos_transporte';

    protected $fillable = [
        'id', 'hombre', 'mujer', 'nna_hombre', 'nna_mujer', 'observacion', 'fecha_ingreso', 'fecha_salida', 'fk_registro_casos_asistencia', 'fecha'
    ];
}