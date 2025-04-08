<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Migrations extends Model {

    protected $table = 'movimiento_migratorio';

    protected $fillable = [
        'id', '_IDREGISTRO', 'nombres', 'apellidos', 'fecha_nacimiento', 'documento_numero', 'identidad_numero', 'sexo',
        'tipo_documento', 'pais_emision', 'nacionalidad', 'fecha_expiracion', 'foto_documento', 'foto_camera',
        'movimiento', 'fecha_registro', 'permitido', 'sincronizado', 'update_Date', 'UUID', 'usuario', 'foto_huella',
        'nfinger_template', 'nombre_equipo', 'nombre_frontera'
    ];
}
