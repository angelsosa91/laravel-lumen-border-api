<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonasSospechosas extends Model {

    protected $table = 'personas_sospechosas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'nombres', 'apellidos', 'fecha_nacimiento', 'nacionalidad', 'tipo_documento', 'sexo', 
        'numero_documento', 'numero_personal', 'documento_fuente', 'quien_configuro', 'informacion', 'accion_requerida', 
        'fecha_cancelacion'
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