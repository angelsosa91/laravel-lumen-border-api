<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistanceStatus;
use App\Models\Cases;
use App\Models\CasesAssistance;
use App\Models\AssisPtm;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssisPTMController extends Controller
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
            ->join('registro_casos_ptm as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
            'rcal.id', 'rcal.comuna', 'rcal.integrantes', 'rcal.banco', 'rcal.cuenta', 'rcal.nro_cuenta', 'rcal.envia_a_as', 'rcal.envia_a_pf', 'rcal.aprobado', 'rcal.nro_pr', 'rcal.oipa', 'rcal.fecha_entrega', 'rcal.pagado', 'rcal.fecha');
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
            $m->fecha_entrega = date("d/m/y", strtotime($m->fecha_entrega));
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
        ->join('registro_casos_ptm as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
        ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis',
        'rcal.id', 'rcal.comuna', 'rcal.integrantes', 'rcal.banco', 'rcal.nro_cuenta', 'rcal.envia_a_as', 'rcal.envia_a_pf', 'rcal.aprobado', 'rcal.nro_pr', 'rcal.oipa', 'rcal.fecha_entrega', 'rcal.pagado', 'rcal.fecha', 'rcal.fecha_entrega as fecha2', 'rcal.fecha as fecha1')
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
            $m->fecha_entrega = date("d/m/y", strtotime($m->fecha_entrega));
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
            $assis->fk_data_tipo_asistencia = 4;
            $assis->save();
            //cases status model
            $assisStatus = new AssistanceStatus;
            $assisStatus->id_registro_casos_asistencia = $assis->id;
            $assisStatus->estado = 'Verificacion';
            $assisStatus->observacion = 'Proceso iniciado';
            $assisStatus->id_user = intval($request->input('v4'));
            $assisStatus->save();
            //model assis
            $ptm = new AssisPtm;
            $ptm->comuna = strtoupper($request->input('v1'));
            $ptm->integrantes = intval($request->input('v2'));
            $ptm->fecha = $request->input('v3');
            $ptm->fk_registro_casos_asistencia = $assis->id;
            $ptm->save();
            //return successful response
            return response()->json(['case' => $case, 'assis' => $assis, 'ptm' => $ptm, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'PTM Created Failed!'], 409);
        }
    }

    //update
    public function updateById(Request $request, $id)
    {
        //try
        try {
            //find
            $ptm = AssisPtm::findOrFail($id);
            $ptm->update([
                'comuna' => strtoupper($request->input('v1')),
                'integrantes' => intval($request->input('v2')),
                'aprobado' => strtoupper($request->input('v3')),
                'nro_pr' => strtoupper($request->input('v4')),
                'oipa' => strtoupper($request->input('v5')),
                'fecha_entrega' => $request->input('v6'),
                'pagado' => strtoupper($request->input('v7')),
                'fecha' => $request->input('v8')
            ]);
            //return successful response
            return response()->json(['PTM' => $ptm, 'message' => 'UPDATED'], 201);
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
            $assis = AssisPtm::findOrFail($id);
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
