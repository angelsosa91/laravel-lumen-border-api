<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model {

    protected $table = 'data_tipo_documentos';

    protected $fillable = [
        'id', 'externalID', 'descripcion', 'update_Date'
    ];
}
