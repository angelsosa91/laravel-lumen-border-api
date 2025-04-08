<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssistanceStatus;
use App\Models\Cases;
use App\Models\CasesAssistance;
use App\Models\CasesStatus;
use App\Models\Entry;
use App\Models\EntryStatus;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CasesController extends Controller
{
    public function show(Request $request)
    {
        //var_dump($request->all()); exit;
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "registro_casos.id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        $estado = ($request->has('estado') and !empty($request->input('estado'))) ? strval($request->input('estado')) : 'En Proceso';
        $user = ($request->has('hasValue')) ? intval(base64_decode($request->input('hasValue'))) : 0;
        $offset = ($page-1)*$rows;
        //filters
        //$search = MovimientoMigratorio::where('id', '>', 0);
        $search = DB::table('registro_casos')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_casos.fk_documentos')
            ->join('personas', 'documentos.fk_personas', '=', 'personas.id')
            ->join('data_paises', 'data_paises.id', '=', 'documentos.fk_data_paises')
            ->join('data_sexo', 'data_sexo.id', '=', 'documentos.fk_data_sexo')
            ->join('registro_entrada as re', 're.id', '=', 'registro_casos.registro_entrada_id')
            ->join('users as u', 'u.id', '=', 're.userid')
            ->select('registro_casos.id', 'registro_casos.created_at as fecha_registro', 'registro_casos.caso_numero', 'registro_casos.estado',
                    'documentos.tipo_documentos', 'documentos.identidad_numero', 'documentos.documento_numero',
                    'documentos.fecha_emision', 'documentos.fecha_expiracion', 'documentos.fecha_nacimiento', 'documentos.apellidos',
                    'documentos.nombres', 'data_sexo.descripcion as sexo', 'documentos.nacionalidad', 'data_paises.descripcion', 'personas.id_persona', 'u.name as usuario');
        //This field uses a LIKE match, handle it separately
        if ($request->has('idpersona') and !empty($request->input('idpersona'))) {
            $search->where('personas.id_persona', $request->input('idpersona'));
        }
        if ($request->has('nombre') and !empty($request->input('nombre'))) {
            $search->where('nombres', 'like', '%' . $request->input('nombre') . '%');
        }
        if ($request->has('apellido') and !empty($request->input('apellido'))) {
            $search->where('apellidos', 'like', '%' . $request->input('apellido') . '%');
        }
        if ($request->has('documento') and !empty($request->input('documento'))) {
            $search->where('documento_numero', 'like', '%' . $request->input('documento') . '%');
        }
        if ($request->has('identidad') and !empty($request->input('identidad'))) {
            $search->orWhere('identidad_numero', 'like', '%' . $request->input('identidad') . '%');
        }
        if ($request->has('genero') and !empty($request->input('genero'))) {
            $search->where('data_sexo.id', $request->input('genero'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween('created_at', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('caso') and !empty($request->input('caso'))) {
            $search->where('registro_casos.id', $request->input('caso'));
        }
        /*if ($request->has('id') and !empty($request->input('id'))) {
            $search->where('registro_casos.id', $request->input('id'));
        }*/
        //get user role if exists
        if($user > 0){
            $users = array();
            $role = DB::table('users')->select('rol')->where('id', $user)->first()->rol;
            if($role == 2){
                $usersId = DB::table('users')->select("id")->where("supervisor", $user);
                if($usersId->count() > 0) {
                    foreach ($usersId->get() as $uid) {
                        $users[] = $uid->id;
                    }
                    $search->whereIn('re.userid', $users);
                }
            } else if($role == 3){
                $users[] = $user;
                $search->whereIn('re.userid', $users);
            }
        }
        //status filter
        $search->where('registro_casos.estado', $estado);
        //count
        $count = $search->count();
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
            $m->fecha_emision = date("d/m/y", strtotime($m->fecha_emision));
            //push
            $items[] = $m;
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showById($id)
    {
        $search = DB::table('registro_casos')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_casos.fk_documentos')
            ->join('personas', 'documentos.fk_personas', '=', 'personas.id')
            ->join('data_paises', 'data_paises.id', '=', 'documentos.fk_data_paises')
            ->join('data_sexo', 'data_sexo.id', '=', 'documentos.fk_data_sexo')
            ->select('registro_casos.id', 'registro_casos.created_at as fecha_registro', 'registro_casos.caso_numero', 'registro_casos.estado',
                    'documentos.tipo_documentos', 'documentos.identidad_numero', 'documentos.documento_numero',
                    'documentos.fecha_emision', 'documentos.fecha_expiracion', 'documentos.fecha_nacimiento', 'documentos.apellidos',
                    'documentos.nombres', 'data_sexo.descripcion as sexo', 'documentos.nacionalidad', 'data_paises.descripcion', 'personas.id_persona', 'registro_casos.registro_entrada_id')
            ->where('registro_casos.id', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
            $m->fecha_emision = date("d/m/y", strtotime($m->fecha_emision));
            //push
            $items[] = $m;
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showCasesStatusById($id)
    {
        $search = DB::table('registro_casos_flujo')
            ->join('users', 'users.id', '=', 'registro_casos_flujo.id_user')
            ->select('registro_casos_flujo.estado', 'registro_casos_flujo.observacion', 'registro_casos_flujo.created_at', 'users.name')
            ->where('registro_casos_flujo.id_registro_casos', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->created_at = date("d/m/y", strtotime($m->created_at));
            //push
            $items[] = $m;
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showAssisStatusById($id)
    {
        $search = DB::table('registro_casos_asistencia_flujo')
            ->join('users', 'users.id', '=', 'registro_casos_asistencia_flujo.id_user')
            ->select('registro_casos_asistencia_flujo.estado', 'registro_casos_asistencia_flujo.observacion', 'registro_casos_asistencia_flujo.created_at', 'users.name')
            ->where('registro_casos_asistencia_flujo.id_registro_casos_asistencia', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->created_at = date("d/m/y", strtotime($m->created_at));
            //push
            $items[] = $m;
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    //update
    public function createById($id, $op)
    {
        //try
        try {
            //find case
            $case = Cases::findOrFail($id);
            //model assis
            $assis = new CasesAssistance;
            $assis->fk_registro_caso = $case->id;
            $assis->fk_data_tipo_asistencia = intval($op);
            //saassisve
            $assis->save();
            //return successful response
            return response()->json(['case' => $case, 'assis' => $assis, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Created Failed!'], 409);
        }
    }

    //update
    public function closeById(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'estado' => 'required',
            'observacion' => 'required',
            'user' => 'required'
        ]);
        //validar estado de entrada
        if($this->getAssisStatusByEntryId($id) > 0){
            return response()->json(['message' => 'Para finalizar el caso, las asistencias asociadas al mismo, deben estar finalizadas!'], 201);
        }
        //declare
        $user = intval($request->input('user'));
        $observacion = strtoupper($request->input('observacion'));
        $estado = $request->input('estado');
        //try
        try {
            //find
            $case = Cases::findOrFail($id);
            $case->update([
                'estado' => $estado
            ]);
            //cases status model
            $caseStatus = new CasesStatus;
            $caseStatus->id_registro_casos = $id;
            $caseStatus->estado = $estado;
            $caseStatus->observacion = $observacion;
            $caseStatus->id_user = $user;
            $caseStatus->save();
            //entry update if case is finished
            //if($estado == 'Finalizado'){
            //entry update
            $entry = Entry::findOrFail($case->registro_entrada_id);
            $entry->update([
                'estado' => $estado
            ]);
            //entry status insert
            $entryStatus = new EntryStatus;
            $entryStatus->id_registro_entrada = $entry->id;
            $entryStatus->estado = $estado;
            $entryStatus->observacion = $observacion;
            $entryStatus->id_user = $user;
            $entryStatus->save();
            //}
            //return successful response
            return response()->json(['Case' => $case, 'message' => 'UPDATED'], 200);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Case Updated Failed!'], 409);
        }
    }

    public function closeAssisById(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'estado' => 'required',
            'observacion' => 'required',
            'user' => 'required'
        ]);
        //declare
        $user = intval($request->input('user'));
        $observacion = strtoupper($request->input('observacion'));
        $estado = $request->input('estado');
        //try
        try {
            //find
            $assis = CasesAssistance::findOrFail($id);
            $assis->update([
                'estado' => $estado
            ]);
            //cases status model
            $assisStatus = new AssistanceStatus();
            $assisStatus->id_registro_casos_asistencia = $id;
            $assisStatus->estado = $estado;
            $assisStatus->observacion = $observacion;
            $assisStatus->id_user = $user;
            $assisStatus->save();
            //return successful response
            return response()->json(['Assis' => $assis, 'message' => 'UPDATED'], 200);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Updated Failed!'], 409);
        }
    }

    public function showAssisByCaseId($id)
    {
        $search = DB::table('registro_casos_asistencia')
            ->join('data_tipo_asistencia', 'data_tipo_asistencia.id', '=', 'registro_casos_asistencia.fk_data_tipo_asistencia')
            ->select('registro_casos_asistencia.id', 'registro_casos_asistencia.fk_registro_caso as caso', 'data_tipo_asistencia.descripcion', 'registro_casos_asistencia.created_at', 'registro_casos_asistencia.updated_at')
            ->where('registro_casos_asistencia.fk_registro_caso', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->caseid = $m->caso.$m->id;
            $m->created_at = date("d/m/y H:i", strtotime($m->created_at));
            $m->updated_at = date("d/m/y H:i", strtotime($m->updated_at));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showAllAssis(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "rc.id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "desc";
        $user = ($request->has('hasValue')) ? intval(base64_decode($request->input('hasValue'))) : 0;
        $offset = ($page-1)*$rows;
        //query data
        $search = DB::table('registro_casos as rc')
            ->join('documentos as d', 'd.id_documento', '=', 'rc.fk_documentos')
            ->join('registro_casos_asistencia as rca', 'rc.id', '=', 'rca.fk_registro_caso')
            ->join('data_tipo_asistencia as dta', 'dta.id', '=', 'rca.fk_data_tipo_asistencia')
            ->join('registro_entrada as re', 're.id', '=', 'rc.registro_entrada_id')
            ->join('users as u', 'u.id', '=', 're.userid')
            ->select('rc.id as id_caso', 'rca.id as id_rasis', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rc.created_at as fecha', 'rc.estado as estado_caso', 'rca.estado', 'd.tipo_documentos', 'd.sexo', 'u.name as usuario');
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
        if ($request->has('estado') and !empty($request->input('estado'))) {
            $search->where('rc.estado', $request->input('estado'));
        } else {
            $search->where('rc.estado', '=', 'En Proceso');
        }
        if ($request->has('desde') and $request->has('hasta')  and !empty($request->input('desde')) and !empty($request->input('hasta'))) {
            $search->whereBetween('fecha', [$request->input('desde'), $request->input('hasta')]);
        }
        //get user role if exists
        if($user > 0){
            $users = array();
            $role = DB::table('users')->select('rol')->where('id', $user)->first()->rol;
            if($role == 2){
                $usersId = DB::table('users')->select("id")->where("supervisor", $user);
                if($usersId->count() > 0) {
                    foreach ($usersId->get() as $uid) {
                        $users[] = $uid->id;
                    }
                    $search->whereIn('re.userid', $users);
                }
            } else if($role == 3){
                $users[] = $user;
                $search->whereIn('re.userid', $users);
            }
        }
        //count
        $count = $search->count();
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql();
        //echo $mov; exit;
        //array
        $result = $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha = date("d/m/y H:i", strtotime($m->fecha));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showAllAssisById($id)
    {
        //query data
        $search = DB::table('registro_casos as rc')
            ->join('documentos as d', 'd.id_documento', '=', 'rc.fk_documentos')
            ->join('registro_casos_asistencia as rca', 'rc.id', '=', 'rca.fk_registro_caso')
            ->join('data_tipo_asistencia as dta', 'dta.id', '=', 'rca.fk_data_tipo_asistencia')
            ->select('rc.id as id_caso', 'rca.id as id_rasis', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rc.created_at as fecha', 'rc.estado as estado_caso', 'rca.estado', 'd.tipo_documentos', 'd.sexo')
            ->where('rc.id', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha = date("d/m/y H:i", strtotime($m->fecha));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showOneAssisById($id)
    {
        $idCase = $idAssistance = 0;
        //query data
        $search = DB::table('registro_casos as rc')
            ->join('documentos as d', 'd.id_documento', '=', 'rc.fk_documentos')
            ->join('registro_casos_asistencia as rca', 'rc.id', '=', 'rca.fk_registro_caso')
            ->join('data_tipo_asistencia as dta', 'dta.id', '=', 'rca.fk_data_tipo_asistencia')
            ->select('rc.id as id_caso', 'rca.id as id_rasis', 'd.nombres', 'd.apellidos', 'd.nacionalidad', 'dta.id as id_asistencia', 'dta.descripcion as asistencia', 'rc.created_at as fecha_caso', 'rca.created_at as fecha_asis', 'rc.estado as estado_caso', 'rca.estado as estado_asis', 'd.tipo_documentos', 'd.sexo', 'rc.caso_numero')
            ->where('rca.id', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = $items = $bank = $asis = array();
        //fetch
        foreach ($mov as $m) {
            $idCase = $m->id_caso;
            $idAssistance = $m->id_asistencia;
            $m->fecha_caso = date("d/m/y H:i", strtotime($m->fecha_caso));
            $m->fecha_asis = date("d/m/y H:i", strtotime($m->fecha_asis));
            //push
            $items[] = $m;
        }
        //banco
        $acc = DB::table('registro_casos_banco')
            ->select('banco', 'cuenta')
            ->where('fk_registro_casos', $idCase)
            ->get();
        //fetch
        foreach ($acc as $a) {
            //push
            $bank[] = $a;
        }
        //asistencia
        if($idAssistance == 3){
            $as = DB::table('registro_casos_subsidio')
                ->select('nro_pr', 'fecha_entrega')
                ->where('fk_registro_casos_asistencia', $id)
                ->get();
            //fetch
            foreach ($as as $a) {
                $a->fecha_entrega = date("d/m/y", strtotime($a->fecha_entrega));
                //push
                $asis[] = $a;
            }
        }
        if($idAssistance == 4){
            $as = DB::table('registro_casos_ptm')
                ->select('nro_pr', 'fecha_entrega', 'oipa')
                ->where('fk_registro_casos_asistencia', $id)
                ->get();
            //fetch
            foreach ($as as $a) {
                $a->fecha_entrega = date("d/m/y", strtotime($a->fecha_entrega));
                //push
                $asis[] = $a;
            }
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        $result["bank"] = $bank;
        $result["asis"] = $asis;
        //return
        return response()->json($result, 200);
    }

    //delete
    public function deleteAssisByCaseId($id)
    {
        //try
        try {
            //find
            $assis = CasesAssistance::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Deleted Failed!'], 409);
        }
    }

    //delete
    public function deleteAssisById($id)
    {
        //try
        try {
            //find
            $assis = CasesAssistance::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis Deleted Failed!'], 409);
        }
    }

    public function uploadFile(Request $request, $id) {
        //Move Uploaded File to public folder
        try {
            $destinationPath = public_path('files');
            if($request->hasFile('archivo')){
                $allowedfileExtension=['pdf','jpg','png','docx'];
                //$file = $request->file('archivo');
                $filename = auth()->id() . '_' . time() . '.'. $request->archivo->extension(); //$file->getClientOriginalName();
                $extension = $request->archivo->extension(); // $file->getClientOriginalExtension();
                $mime = $request->archivo->getClientMimeType();
                $check=in_array($extension,$allowedfileExtension);
                //dd($check);
                if($check){
                    $request->archivo->move($destinationPath, $filename);
                    //find
                    $case = Cases::findOrFail($id);
                    $case->update([
                        'path' => $filename,
                        'extension' => $mime
                    ]);
                    //return successful response
                    return response()->json(['case' => $case, 'message' => 'UPDATED'], 201);
                } else {
                    return response()->json(['message' => 'Upload File Failed!'], 409);
                }
            }
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Upload File Failed!'], 409);
        }
    }

    public function viewFile($id){
        try{
            $case = Cases::findOrFail($id);
            if(strlen($case->path) == 0)
                return response()->json(['file' => ''], 200);
            else
                return response()->json(['file' => base64_encode(File::get(public_path('files/'.$case->path))), 'extension' => $case->extension, 'message' => 'DOWNLOAD'], 200);
            //return File::get(public_path('files/'.$case->path)); // response()->file(public_path('files/'.$case->path));
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Get File Failed!'], 422);
        }
    }

    public function viewFaceCam($id){
        try{
            $search = DB::table('registro_entrada_bio as reb')
            ->join('registro_entrada as re', 're.id', '=', 'reb.fk_registro_entrada')
            ->join('registro_casos as rc', 'rc.registro_entrada_id', '=', 're.id')
            ->select('reb.foto_documento as document', 'reb.foto_camera as face', 'reb.foto_huella as finger')
            ->where('rc.id', $id);
            //echo $search->toSql(); exit;
            if($search->count() > 0) {
                $data = $search->get()[0];
                return response()->json(['face' => $data->face, 'document' => $data->document, 'finger' => $data->finger], 200);
            } else {
                return response()->json(['face' => 'empty', 'document' => 'empty', 'finger' => 'empty'], 200);
            }
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Get File Failed!'], 422);
        }
    }

    public function getAssisStatusByEntryId($id)
    {
        return DB::table('registro_casos_asistencia')
            ->select('id')
            ->where('fk_registro_caso', '=', $id)
            ->whereIn('estado', array('Verificacion', 'Validacion', 'Autorizacion'))->count();
    }
}
