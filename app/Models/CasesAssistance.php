<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CasesAssistance extends Model {

    /**
     * @var int|mixed
     */
    protected $table = 'registro_casos_asistencia';

    protected $fillable = [
        'id', 'fk_registro_caso', 'fk_data_tipo_asistencia', 'estado'
    ];
}
