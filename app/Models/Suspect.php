<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Suspect extends Model {

    protected $table = 'personas_sospechosas';

    protected $fillable = [
        'id', 'nombres', 'apellidos', 'fecha_nacimiento', 'nacionalidad', 'tipo_documento', 'sexo',
        'numero_documento', 'numero_personal', 'documento_fuente', 'quien_configuro', 'informacion', 'accion_requerida',
        'fecha_cancelacion'
    ];
}
