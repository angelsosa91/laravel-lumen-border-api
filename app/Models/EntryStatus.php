<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryStatus extends Model {

    /**
     * @var bool|mixed
     */
    protected $table = 'registro_entrada_flujo';

    protected $fillable = [
        'id', 'id_registro_entrada', 'estado', 'observacion', 'id_user'
    ];
}
