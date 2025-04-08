<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Border extends Model {

    protected $table = 'data_fronteras';

    protected $fillable = [
        'id', 'identificador', 'descripcion', 'estado'
    ];
}
