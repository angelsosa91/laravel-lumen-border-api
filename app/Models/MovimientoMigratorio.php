<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoMigratorio extends Model {

    protected $table = 'movimiento_migratorio';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', '_IDREGISTRO', 'nombres', 'apellidos', 'fecha_nacimiento', 'documento_numero', 'identidad_numero', 'sexo', 
        'tipo_documento', 'pais_emision', 'nacionalidad', 'fecha_expiracion', 'foto_documento', 'foto_camera', 
        'movimiento', 'fecha_registro', 'permitido', 'sincronizado', 'update_Date', 'UUID', 'usuario', 'foto_huella', 
        'nfinger_template', 'nombre_equipo', 'nombre_frontera'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    /*
    protected $hidden = [
        'password',
    ];
    */
}