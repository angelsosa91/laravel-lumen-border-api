<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Folio extends Model {

    protected $table = 'data_informacion_folleteria';

    protected $fillable = ['id', 'descripcion', 'estado'];
}
