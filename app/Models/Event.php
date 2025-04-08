<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model {

    protected $table = 'sincronizacion_frontera';

    protected $fillable = [
        'id', 'nombre_frontera', 'fecha_ultimo_movimiento', 'fecha_registro', 'tipo_sincronizacion', 'telefono_movil'
    ];
}
