<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model {

    protected $table = 'movimiento_alertas';

    protected $fillable = [
        'id', 'fk_movimiento_migratorio', 'fecha_registro', 'supervisor_ip', 'estado', 'fk_personas_sospechosas',
        'supervisor_nota', 'accion_tomada', 'motivo_coincidencia', 'supervisor_usuario'
    ];
}
