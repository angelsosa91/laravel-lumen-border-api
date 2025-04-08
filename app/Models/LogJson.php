<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogJson extends Model {

    protected $table = 'log_json';

    protected $fillable = [
        'idlog_json', 'json', 'mobil_uuid', 'mobil_nombre'
    ];
}
