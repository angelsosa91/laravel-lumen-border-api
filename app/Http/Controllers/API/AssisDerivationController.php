<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistanceStatus;
use App\Models\Cases;
use App\Models\CasesAssistance;
use App\Models\AssisDerivation;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssisDerivationController extends Controller
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
            ->join('registro_casos_derivacion as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis', 'rcal.id', 'rcal.institucion', 'rcal.motivo');
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
        /*if ($request->has('desde') and $request->has('hasta')  and !empty($request->input('desde')) and !empty($request->input('hasta'))) {
            $search->whereBetween('fecha', [$request->input('desde'), $request->input('hasta')]);
        }*/
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            //$m->fecha = date("d/m/y", strtotime($m->fecha));
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
            ->join('registro_casos_derivacion as rcal', 'rca.id', '=', 'rcal.fk_registro_casos_asistencia')
            ->select('rc.id as id_caso', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rca.id as id_rasis', 'rcal.id', 'rcal.institucion', 'rcal.motivo')
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
            //$m->fecha = date("d/m/y", strtotime($m->fecha));
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
        //validate incoming request
        $this->validate($request, [
            'v1' => 'required',
            'v2' => 'required',
            'v3' => 'required'
        ]);
        //try
        try {
            //find case
            $case = Cases::findOrFail($id);
            //model assis
            $assis = new CasesAssistance;
            $assis->fk_registro_caso = $case->id;
            $assis->fk_data_tipo_asistencia = 6;
            $assis->save();
            //cases status model
            $assisStatus = new AssistanceStatus;
            $assisStatus->id_registro_casos_asistencia = $assis->id;
            $assisStatus->estado = 'Verificacion';
            $assisStatus->observacion = 'Proceso iniciado';
            $assisStatus->id_user = intval($request->input('v3'));
            $assisStatus->save();
            //model assis
            $deri = new AssisDerivation;
            $deri->institucion = strtoupper($request->input('v1'));
            $deri->motivo = strtoupper($request->input('v2'));
            $deri->fk_registro_casos_asistencia = $assis->id;
            $deri->save();
            //return successful response
            return response()->json(['case' => $case, 'assis' => $assis, 'deri' => $deri, 'message' => 'CREATED'], 201);
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
        $input = $request->all();

        $validator = Validator::make($input, [
            'v1' => 'required',
            'v2' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //try
        try {
            //find
            $deri = AssisDerivation::findOrFail($id);
            $deri->update([
                'institucion' => strtoupper($request->input('v1')),
                'motivo' => strtoupper($request->input('v2'))
            ]);
            //return successful response
            return response()->json(['deri' => $deri, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Host Updated Failed!'], 409);
        }
    }

    //delete
    public function deleteById($id)
    {
        //try
        try {
            //find
            $deri = AssisDerivation::findOrFail($id);
            $deri->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Deleted Failed!'], 409);
        }
    }
}
