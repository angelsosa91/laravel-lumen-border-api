<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model {

    protected $table = 'data_estado_civil';

    protected $fillable = [
        'id', 'estado_civil', 'descripcion'
    ];
}