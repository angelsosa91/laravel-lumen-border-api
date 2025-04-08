<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistanceStatus extends Model {

    /**
     * @var bool|mixed
     */
    protected $table = 'registro_casos_asistencia_flujo';

    protected $fillable = [
        'id', 'id_registro_casos_asistencia', 'estado', 'observacion', 'id_user'
    ];
}
