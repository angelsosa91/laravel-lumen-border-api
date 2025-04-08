<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eventos extends Model {

    protected $table = 'sincronizacion_frontera';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'nombre_frontera', 'fecha_ultimo_movimiento', 'fecha_registro', 'tipo_sincronizacion', 'telefono_movil'
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