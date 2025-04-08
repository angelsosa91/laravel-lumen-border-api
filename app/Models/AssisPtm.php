<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisPtm extends Model {

    protected $table = 'registro_casos_ptm';

    protected $fillable = [
        'id', 'comuna', 'integrantes', 'banco', 'cuenta', 'nro_cuenta', 'envia_a_as', 'envia_a_pf', 'aprobado', 'nro_pr', 'oipa', 'fecha_entrega', 'pagado', 'fk_registro_casos_asistencia', 'fecha'
    ];
}