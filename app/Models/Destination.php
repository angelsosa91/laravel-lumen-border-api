<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model {

    protected $table = 'data_destino_final';

    protected $fillable = [
        'id', 'identificador', 'descripcion', 'estado'
    ];
}
