<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sex extends Model {

    protected $table = 'data_sexo';

    protected $fillable = [
        'id', 'sexo', 'descripcion'
    ];
}