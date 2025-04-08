<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistanceStatus;
use App\Models\Cases;
use App\Models\CasesAssistance;
use App\Models\AssisKit;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssisKitController extends Controller
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
            ->join('registro_casos_kits as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
            'rcal.id', 'rcal.mujeres', 'rcal.hombres', 'rcal.ninos', 'rcal.ninas', 'rcal.higiene_1', 'rcal.alimentacion_1', 'rcal.higiene_2', 'rcal.alimentacion_2', 'rcal.panales', 'rcal.observacion', 'rcal.fecha');
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
            ->join('registro_casos_kits as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
            'rcal.id', 'rcal.mujeres', 'rcal.hombres', 'rcal.ninos', 'rcal.ninas', 'rcal.higiene_1', 'rcal.alimentacion_1', 'rcal.higiene_2', 'rcal.alimentacion_2', 'rcal.panales', 'rcal.observacion', 'rcal.fecha', 'rcal.fecha as fecha1')
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
            $assis->fk_data_tipo_asistencia = 5;
            $assis->save();
            //cases status model
            $assisStatus = new AssistanceStatus;
            $assisStatus->id_registro_casos_asistencia = $assis->id;
            $assisStatus->estado = 'Verificacion';
            $assisStatus->observacion = 'Proceso iniciado';
            $assisStatus->id_user = intval($request->input('v10'));
            $assisStatus->save();
            //model assis
            $kit = new AssisKit;
            $kit->mujeres = intval($request->input('v1'));
            $kit->hombres = intval($request->input('v2'));
            $kit->ninos = intval($request->input('v3'));
            $kit->ninas = intval($request->input('v4'));
            $kit->higiene_1 = intval($request->input('v5'));
            $kit->alimentacion_1 = intval($request->input('v6'));
            $kit->panales = intval($request->input('v7'));
            $kit->observacion = strtoupper($request->input('v8'));
            $kit->fecha = $request->input('v9');
            $kit->fk_registro_casos_asistencia = $assis->id;
            $kit->save();
            //return successful response
            return response()->json(['case' => $case, 'assis' => $assis, 'kit' => $kit, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            echo $e->getMessage();
            return response()->json(['message' => 'KIT Created Failed!'], 409);
        }
    }

    //update
    public function updateById(Request $request, $id)
    {
        //try
        try {
            //find
            $kit = AssisKit::findOrFail($id);
            $kit->update([
                'mujeres' => intval($request->input('v1')),
                'hombres' => intval($request->input('v2')),
                'ninos' => intval($request->input('v3')),
                'ninas' => intval($request->input('v4')),
                'higiene_1' => intval($request->input('v5')),
                'alimentacion_1' => intval($request->input('v6')),
                'panales' => intval($request->input('v7')),
                'observacion' => strtoupper($request->input('v8')),
                'fecha' => $request->input('v9')
            ]);
            //return successful response
            return response()->json(['Kit' => $kit, 'message' => 'UPDATED'], 201);
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
            $assis = AssisKit::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Deleted Failed!'], 409);
        }
    }*/
}
