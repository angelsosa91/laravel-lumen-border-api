<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model {

    protected $table = 'data_diagnostico_necesidad';

    protected $fillable = ['id', 'descripcion', 'estado'];
}
