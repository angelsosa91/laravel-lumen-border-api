<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssisSub extends Model {

    protected $table = 'registro_casos_subsidio';

    protected $fillable = [
        'id', 'comuna', 'integrantes', 'direccion', 'gestion', 'envia_a_as', 'envia_a_pf', 'aprobado', 'nro_pr', 'fecha_entrega', 'pagado', 'fk_registro_casos_asistencia', 'fecha'
    ];
}