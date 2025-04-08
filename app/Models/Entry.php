<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model {

    protected $table = 'registro_entrada';

    protected $fillable = [
        'id', 'fecha_atencion', 'id_region', 'lugar_atencion', 'fk_documentos', 'situacion_migratoria', 'grupo_familiar', 'contacto_tel', 'cesfam_ins', 'tiene_ipe', 'motivo_consulta', 'asistencia_humanitaria', 'transporte_hum', 'transporte_nombre_apoyo', 'transporte_telefono_apoyo', 'transporte_direccion_apoyo', 'alojamiento_consulta', 'alojamiento_tipodoc_familia', 'alojamiento_mascota', 'usuario_folleteria', 'usuario_asistencia', 'usuario_derivacion', 'derivacion_institucion', 'derivacion_motivo', 'derivacion_firma', 'reporta_caso', 'estado', 'id_persona', 'comentarios', 'prioridad', 'username', 'userid'
    ];
}
