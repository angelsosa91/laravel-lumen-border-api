<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cases extends Model {

    protected $table = 'registro_casos';

    protected $fillable = [
        'id', 'fk_documentos', 'registro_entrada_id', 'caso_numero', 'estado', 'path', 'extension'
    ];
}
