<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasesStatus extends Model {

    /**
     * @var bool|mixed
     */
    protected $table = 'registro_casos_flujo';

    protected $fillable = [
        'id', 'id_registro_casos', 'estado', 'observacion', 'id_user'
    ];
}
