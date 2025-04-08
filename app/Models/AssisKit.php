<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisKit extends Model {

    protected $table = 'registro_casos_kits';

    protected $fillable = [
        'id', 'mujeres', 'hombres', 'ninos', 'ninas', 'higiene_1', 'alimentacion_1', 'higiene_2', 'alimentacion_2', 'panales', 'observacion', 'fk_registro_casos_asistencia', 'fecha' 
    ];
}