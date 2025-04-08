<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model {

    protected $table = 'data_paises';

    protected $fillable = [
        'id', 'identificador', 'descripcion', 'estado'
    ];
}
