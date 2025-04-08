<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistanceStatus;
use App\Models\Cases;
use App\Models\CasesAssistance;
use App\Models\AssisHost;
use App\Models\AssisHostExt;
use App\Models\CasesStatus;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssisHostController extends Controller
{
    public function show(Request $request)
    {
        //var_dump($request->all()); exit;
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "rc.id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "desc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        //$search = MovimientoMigratorio::where('id', '>', 0);
        $search = DB::table('registro_casos as rc')
            ->join('documentos as d', 'd.id_documento', '=', 'rc.fk_documentos')
            ->join('registro_casos_asistencia as rca', 'rc.id', '=', 'rca.fk_registro_caso')
            ->join('data_tipo_asistencia as dta', 'dta.id', '=', 'rca.fk_data_tipo_asistencia')
            ->join('registro_casos_alojamiento as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
            'rcal.id', 'rcal.hombre', 'rcal.mujer', 'rcal.nna_hombre', 'rcal.nna_mujer', 'rcal.observacion', 'rcal.fecha_ingreso', 'rcal.fecha_salida', 'rcal.fecha', 'rcal.grupo');
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('nombre') and !empty($request->input('nombre'))) {
            $search->where('d.nombres', 'like', '%' . $request->input('nombre') . '%');
        }
        if ($request->has('apellido') and !empty($request->input('apellido'))) {
            $search->where('d.apellidos', 'like', '%' . $request->input('apellido') . '%');
        }
        if ($request->has('caso') and !empty($request->input('caso'))) {
            $search->where('rc.id', $request->input('caso'));
        }
        if ($request->has('desde') and $request->has('hasta')  and !empty($request->input('desde')) and !empty($request->input('hasta'))) {
            $search->whereBetween('fecha', [$request->input('desde'), $request->input('hasta')]);
        }
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_ingreso = date("d/m/y", strtotime($m->fecha_ingreso));
            $m->fecha_salida = date("d/m/y", strtotime($m->fecha_salida));
            $m->fecha = date("d/m/y", strtotime($m->fecha));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showById($id)
    {
        $search = DB::table('registro_casos as rc')
            ->join('documentos as d', 'd.id_documento', '=', 'rc.fk_documentos')
            ->join('registro_casos_asistencia as rca', 'rc.id', '=', 'rca.fk_registro_caso')
            ->join('data_tipo_asistencia as dta', 'dta.id', '=', 'rca.fk_data_tipo_asistencia')
            ->join('registro_casos_alojamiento as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
            'rcal.id', 'rcal.hombre', 'rcal.mujer', 'rcal.nna_hombre', 'rcal.nna_mujer', 'rcal.observacion', 'rcal.fecha_ingreso', 'rcal.fecha_salida', 'rcal.fecha', 'rcal.grupo', 'rcal.fecha_ingreso as fecha2', 'rcal.fecha_salida as fecha3', 'rcal.fecha as fecha1')
            ->where('rc.id', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_ingreso = date("d/m/y", strtotime($m->fecha_ingreso));
            $m->fecha_salida = date("d/m/y", strtotime($m->fecha_salida));
            $m->fecha = date("d/m/y", strtotime($m->fecha));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    //create
    public function createById(Request $request, $id)
    {
        //try
        try {
            //find case
            $case = Cases::findOrFail($id);
            //model assis
            $assis = new CasesAssistance;
            $assis->fk_registro_caso = $case->id;
            $assis->fk_data_tipo_asistencia = 1;
            $assis->save();
            //cases status model
            $assisStatus = new AssistanceStatus;
            $assisStatus->id_registro_casos_asistencia = $assis->id;
            $assisStatus->estado = 'Verificacion';
            $assisStatus->observacion = 'Proceso iniciado';
            $assisStatus->id_user = intval($request->input('v7'));
            $assisStatus->save();
            //model assis
            $host = new AssisHost;
            $host->hombre = intval($request->input('v1'));
            $host->mujer = intval($request->input('v2'));
            $host->nna_hombre = intval($request->input('v3'));
            $host->nna_mujer = intval($request->input('v4'));
            $host->observacion = strtoupper($request->input('v5'));
            $host->fecha = $request->input('v6');
            $host->fk_registro_casos_asistencia = $assis->id;
            $host->save();
            //return successful response
            return response()->json(['case' => $case, 'assis' => $assis, 'host' => $host, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Host Created Failed!'], 409);
        }
    }

    //update
    public function updateById(Request $request, $id)
    {
        //try
        try {
            //find
            $host = AssisHost::findOrFail($id);
            $host->update([
                'hombre' => intval($request->input('v1')),
                'mujer' => intval($request->input('v2')),
                'nna_hombre' => intval($request->input('v3')),
                'nna_mujer' => intval($request->input('v4')),
                'observacion' => strtoupper($request->input('v5')),
                'fecha_ingreso' => $request->input('v6'),
                'fecha_salida' => $request->input('v7'),
                'fecha' => $request->input('v8')
            ]);
            //return successful response
            return response()->json(['Host' => $host, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Host Updated Failed!'], 409);
        }
    }

    //delete
    /*public function deleteById($id)
    {
        //try
        try {
            //find
            $assis = AssisHost::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Deleted Failed!'], 409);
        }
    }*/

    //extension zone begin
    //get
    public function showExtensionById($id)
    {
        $search = DB::table('registro_casos_alojamiento_extension')
            ->select('id', 'fk_registro_casos_alojamiento', 'fecha_extension', 'fecha_salida', 'nro_pr', 'grupo', 'fecha_extension as fecha1', 'fecha_salida as fecha2')
            ->where('fk_registro_casos_alojamiento', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_extension = date("d/m/y", strtotime($m->fecha_extension));
            $m->fecha_salida = date("d/m/y", strtotime($m->fecha_salida));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    //create
    public function createExtensionById(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'v1' => 'required',
            'v2' => 'required',
            'v3' => 'required',
            'v4' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }
        //try
        try {
            //model assis
            $hostExt = new AssisHostExt;
            $hostExt->fk_registro_casos_alojamiento = intval($id);
            $hostExt->fecha_extension = $request->input('v1');
            $hostExt->fecha_salida = $request->input('v2');
            $hostExt->nro_pr = strtoupper($request->input('v3'));
            $hostExt->grupo = strtoupper($request->input('v4'));
            $hostExt->save();
            //return successful response
            return response()->json(['hostExt' => $hostExt, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            echo $e->getMessage();
            return response()->json(['message' => 'Host Created Failed!'], 409);
        }
    }

    //update
    public function updateExtensionById(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'v1' => 'required',
            'v2' => 'required',
            'v3' => 'required',
            'v4' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //try
        try {
            //find
            $hostExt = AssisHostExt::findOrFail($id);
            $hostExt->update([
                'fecha_extension' => $request->input('v1'),
                'fecha_salida' => $request->input('v2'),
                'nro_pr' => strtoupper($request->input('v3')),
                'grupo' => strtoupper($request->input('v4'))
            ]);
            //return successful response
            return response()->json(['HostExt' => $hostExt, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Host Updated Failed!'], 409);
        }
    }

    //delete
    public function deleteExtensionById($id)
    {
        //try
        try {
            //find
            $assis = AssisHostExt::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'HostExt Deleted Failed!'], 409);
        }
    }
}
