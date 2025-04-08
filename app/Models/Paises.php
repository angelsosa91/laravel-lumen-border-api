<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paises extends Model {

    protected $table = 'data_paises';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'identificador', 'descripcion', 'estado'
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