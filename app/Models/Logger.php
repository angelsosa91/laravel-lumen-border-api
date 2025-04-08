<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logger extends Model {

    protected $table = 'data_logger';

    protected $fillable = [
        'id', 'name', 'descripcion', 'status'
    ];
}
