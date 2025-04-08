<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InfoController extends Controller
{
	//select with docs
	public function show($id)
    {
        //filters
        $search = DB::table('info_salud')
            ->select('id', 'condicion', 'enfermedad', 'enfermedad_condicion_urgente', 'vacunacion_covid19', 'fecha_registro')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByCondition($id)
    {
        //filters
        $search = DB::table('info_salud_condiciones')
            ->select('id', 'condicion', 'marcado')
            ->where('id_info_salud', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByDisease($id)
    {
        //filters
        $search = DB::table('info_salud_enfermedades')
            ->select('id', 'descripcion', 'marcado')
            ->where('id_info_salud', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByTravel($id)
    {
        //filters
        $search = DB::table('info_viaje')
            ->join('data_fronteras', 'data_fronteras.id', '=', 'info_viaje.fk_data_frontera')
            ->join('data_destino_final', 'data_destino_final.id', '=', 'info_viaje.fk_data_destino_final')
            ->select('info_viaje.id', 'info_viaje.fecha_registro', 'fecha_ingreso', 'situacion_sufrida',  
                    'documento_fisico', 'data_fronteras.descripcion as frontera', 'data_destino_final.descripcion as destino', 'info_viaje.estado',)
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_ingreso = date("d/m/y H:i", strtotime($m->fecha_ingreso));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByService($id)
    {
        //filters
        $search = DB::table('info_servicio_derivacion')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByEducation($id)
    {
        //filters
        $search = DB::table('info_educacion')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByPregnancy($id)
    {
        //filters
        $search = DB::table('info_embarazo')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showBySecurity($id)
    {
        //filters
        $search = DB::table('info_proteccion')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showByNetwork($id)
    {
        //filters
        $search = DB::table('info_red_apoyo')
            ->where('fk_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
}
