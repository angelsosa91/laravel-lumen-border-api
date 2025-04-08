<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gender extends Model {

    protected $table = 'data_genero';

    protected $fillable = [
        'id', 'sexo', 'descripcion'
    ];
}
