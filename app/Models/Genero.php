<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genero extends Model {

    protected $table = 'data_genero';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'sexo', 'descripcion'
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