<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model {

    protected $table = 'registro_casos_banco';

    protected $fillable = [
        'id', 'fk_registro_casos', 'banco', 'cuenta'
    ];
}
