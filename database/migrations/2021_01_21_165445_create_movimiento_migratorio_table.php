<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoMigratorioTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_migratorio', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('_IDREGISTRO', 45)->unique();
            $table->string('nombres', 45);
            $table->string('apellidos', 45);
            $table->date('fecha_nacimiento');
            $table->string('documento_numero', 45);
            $table->string('identidad_numero', 45);
            $table->string('sexo', 45);
            $table->string('tipo_documento', 200);
            $table->string('pais_emision', 200);
            $table->string('nacionalidad', 10);
            $table->date('fecha_expiracion');
            $table->longText('foto_documento');
            $table->longText('foto_camera');
            $table->string('movimiento', 10);
            $table->dateTime('fecha_registro');
            $table->string('permitido', 10);
            $table->string('sincronizado', 10);
            $table->dateTime('update_Date');
            $table->string('UUID', 200);
            $table->string('usuario', 45);
            $table->longText('foto_huella');
            $table->longText('nfinger_template');
            $table->string('nombre_equipo', 200);
            $table->string('nombre_frontera', 200);
            $table->timestamps();
            $table->index(['_IDREGISTRO']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimiento_migratorio');
    }
}
