<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Privileges extends Model {

    protected $table = 'privileges';

    protected $fillable = [
        'id', 'id_rol', 'id_module', 'access', 'status', 'read', 'write', 'update', 'delete'
    ];
}
