<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\Entry;
use App\Models\EntryStatus;
use App\Models\CasesStatus;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntryController extends Controller
{
    public function show(Request $request)
    {
        //var_dump($request->all()); exit;
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "registro_entrada.id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "desc";
        $estado = ($request->has('estado') and !empty($request->input('estado'))) ? [strval($request->input('estado'))] : ['Pendiente', 'Activado'];
        $user = ($request->has('hasValue')) ? intval(base64_decode($request->input('hasValue'))) : 0;
        $offset = ($page-1)*$rows;
        //$search = MovimientoMigratorio::where('id', '>', 0);
        $search = DB::table('registro_entrada')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_entrada.fk_documentos')
            ->join('personas', 'documentos.fk_personas', '=', 'personas.id')
            ->join('data_region', 'data_region.id', '=', 'registro_entrada.id_region')
            ->join('data_paises', 'data_paises.id', '=', 'documentos.fk_data_nacionalidad')
            ->join('data_sexo', 'data_sexo.id', '=', 'documentos.fk_data_sexo')
            ->join('users', 'users.id', '=', 'registro_entrada.userid')
            ->select('registro_entrada.*', 'region', 'documento_numero', 'tipo_documentos', 'nombres', 'apellidos', 'data_sexo.descripcion as sexo', 'data_paises.descripcion as nacionalidad', 'users.name as usuario');
        //This field uses a LIKE match, handle it separately
        if ($request->has('nombre') and !empty($request->input('nombre'))) {
            $search->where('nombres', 'like', '%' . $request->input('nombre') . '%');
        }
        if ($request->has('apellido') and !empty($request->input('apellido'))) {
            $search->where('apellidos', 'like', '%' . $request->input('apellido') . '%');
        }
        if ($request->has('documento') and !empty($request->input('documento'))) {
            $search->where('documento_numero', 'like', '%' . $request->input('documento') . '%');
        }
        if ($request->has('genero') and !empty($request->input('genero'))) {
            $search->where('data_sexo.id', $request->input('genero'));
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween('fecha_atencion', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('id') and !empty($request->input('id'))) {
            $search->where('registro_entrada.id', $request->input('id'));
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
                    $search->whereIn('registro_entrada.userid', $users);
                }
            } else if($role == 3){
                $users[] = $user;
                $search->whereIn('registro_entrada.userid', $users);
            }
        }
        //status filter
        $search->whereIn('registro_entrada.estado', $estado);
        //count
        $count = $search->count();
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql()
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->username = ($m->username != null || !empty($m->username) ? $m->username : 'No asignado');
            $m->fecha_atencion = date("d/m/y", strtotime($m->fecha_atencion));
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
        $search = DB::table('registro_entrada')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_entrada.fk_documentos')
            ->join('personas', 'documentos.fk_personas', '=', 'personas.id')
            ->join('data_region', 'data_region.id', '=', 'registro_entrada.id_region')
            ->join('data_paises', 'data_paises.id', '=', 'documentos.fk_data_nacionalidad')
            ->join('data_sexo', 'data_sexo.id', '=', 'documentos.fk_data_sexo')
            ->join('users', 'users.id', '=', 'registro_entrada.userid')
            ->join('data_estado_civil', 'data_estado_civil.id', '=', 'documentos.fk_estado_civil')
            ->select('registro_entrada.*', 'region', 'documento_numero', 'tipo_documentos', 'nombres', 'apellidos', 'data_sexo.descripcion as sexo', 'data_paises.descripcion as nacionalidad', 'users.name as usuario', 'identidad_numero', 'fecha_nacimiento', 'pais_emision', 'data_estado_civil.descripcion as civil')
            ->where('registro_entrada.id', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->username = ($m->username != null || !empty($m->username) ? $m->username : 'No asignado');
            $m->atencion = date("Y-m-d", strtotime($m->fecha_atencion));
            $m->fecha_atencion = date("d/m/y", strtotime($m->fecha_atencion));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    public function showEntryStatusById($id)
    {
        $search = DB::table('registro_entrada_flujo')
            ->join('users', 'users.id', '=', 'registro_entrada_flujo.id_user')
            ->select('registro_entrada_flujo.estado', 'registro_entrada_flujo.observacion', 'registro_entrada_flujo.created_at', 'users.name')
            ->where('registro_entrada_flujo.id_registro_entrada', '=', $id);
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
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    //update
    public function updateById(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'estado' => 'required',
            'observacion' => 'required',
            'user' => 'required'
        ]);
        //validar estado de entrada
        if($this->getStatusByEntryId($id) == 0){
            return response()->json(['message' => 'El registro debe estar en estado Pendiente!'], 201);
        }
        //datos persona
        $personId = $this->getIdPersonByEntryId($id);
        if(empty($personId)){
            return response()->json(['message' => 'Persona no identificada!'], 201);
        }
        //datos caso abierto
        $openCase = $this->getOpenCaseByPersonId($personId);
        if($openCase > 0){
            return response()->json(['message' => 'Sujeto duplicado con caso abierto!, Verifique => ID_PERSONA = '.$personId], 201);
        }
        //try
        try {
            //declare
            $user = intval($request->input('user'));
            $observacion = strtoupper($request->input('observacion'));
            $estado = $request->input('estado');
            $caso = ($request->input('estado') == 'Activado') ? 'SI' : 'NO';
            //find entry
            $entry = Entry::findOrFail($id);
            $entry->update([
                'reporta_caso' => $caso,
                'estado' => $estado
            ]);
            //entry status model
            $entryStatus = new EntryStatus;
            $entryStatus->id_registro_entrada = $entry->id;
            $entryStatus->estado = $estado;
            $entryStatus->observacion = $observacion;
            $entryStatus->id_user = $user;
            $entryStatus->save();
            //if case equals Activado
            if($caso == 'SI') {
                //case model
                $case = new Cases;
                $case->fk_documentos = $entry->fk_documentos;
                $case->caso_numero = Str::uuid()->toString();
                $case->registro_entrada_id = $entry->id;
                $case->estado = 'En Proceso';
                $case->save();
                //case status model
                $caseStatus = new CasesStatus;
                $caseStatus->id_registro_casos = $case->id;
                $caseStatus->estado = 'En Proceso';
                $caseStatus->observacion = 'Proceso iniciado';
                $caseStatus->id_user = $user;
                $caseStatus->save();
            }
            //return successful response
            return response()->json(['entry' => $entry, 'message' => 'UPDATED'], 200);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Entry Updated Failed!'], 409);
        }
    }

    public function updateEntryById(Request $request, $id)
    {
        //inputs
        $nombres = $request->input('nombres');
        $apellidos = $request->input('apellidos');
        $fechaNacimiento = $request->input('fechaNacimiento');
        $documentoNumero = $request->input('documentoNumero');
        $identidadNumero = $request->input('identidadNumero');
        $sexo = $request->input('sexo');
        $tipoDocumento = $request->input('tipoDocumento');
        $paisEmision = $request->input('paisEmision');
        $nacionalidad = $request->input('nacionalidad');
        $fechaAtencion = $request->input('fechaAtencion');
        $idRegion = $request->input('idRegion');
        $lugarAtencion = $request->input('lugarAtencion');
        $situacionMigratoria = $request->input('situacionMigratoria');
        $grupoFamiliar = $request->input('grupoFamiliar');
        $contactoTel = $request->input('contactoTel');
        $cesfamIns = $request->input('cesfamIns');
        $tieneIpe = $request->input('tieneIpe');
        $motivoConsulta = $request->input('motivoConsulta');
        $asistenciaHumanitaria = $request->input('asistenciaHumanitaria');
        $transporteHum = $request->input('transporteHum');
        $transporteNombreApoyo = $request->input('transporteNombreApoyo');
        $transporteTelefonoApoyo = $request->input('transporteTelefonoApoyo');
        $transporteDireccionApoyo = $request->input('transporteDireccionApoyo');
        $alojamientoConsulta = $request->input('alojamientoConsulta');
        $alojamientoMascota = $request->input('alojamientoMascota');
        $usuarioFolleteria = $request->input('usuarioFolleteria');
        $usuarioDerivacion = $request->input('usuarioDerivacion');
        $comentarios = $request->input('comentarios');
        $usuario = $request->input('usuario');
        $estadoCivil = $request->input('estadoCivil');
        $ingreso = $request->input('ingreso');
        $prioridad = $request->input('prioridad');

        //VARIABLES
        $code = '1'; $msg = 'ACTUALIZACION ERRONEA';

        //validar estado de entrada
        if($this->getStatusByEntryId($id) == 0){
            return response()->json(['message' => 'El registro debe estar en estado Pendiente!'], 201);
        }
        //try
        try {
            //query
            DB::statement(DB::raw('CALL sp_update_entry(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @code, @msg)'), array($id, $nombres, $apellidos, $fechaNacimiento, $documentoNumero, $identidadNumero, $sexo, $tipoDocumento, $paisEmision, $nacionalidad, $fechaAtencion, $idRegion, $lugarAtencion, $situacionMigratoria, $grupoFamiliar, $contactoTel, $cesfamIns, $tieneIpe, $motivoConsulta, $asistenciaHumanitaria, $transporteHum, $transporteNombreApoyo, $transporteTelefonoApoyo, $transporteDireccionApoyo, $alojamientoConsulta, $alojamientoMascota, $usuarioFolleteria, $usuarioDerivacion, $usuario, $comentarios, $estadoCivil, $ingreso, $prioridad));
            //return
            $results = DB::select('SELECT @code as status, @msg as message ');
            //return successful response
            return response()->json(['code' => $results[0]->status, 'message' => $results[0]->message], 200);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['code' => $code, 'message' => $msg, 'error' => $e->getMessage()], 409);
        }
    }

    public function getIdPersonByEntryId($id)
    {
        $search = DB::table('registro_entrada')
            ->select('id_persona')
            ->where('id', '=', $id)->get()[0];
        //var_dump($search); exit;
        //return
        return $search->id_persona;
    }

    public function getOpenCaseByPersonId($id)
    {
        return DB::table('registro_entrada as re')
            ->join('registro_casos as rc', 're.id', '=', 'rc.registro_entrada_id')
            ->select('re.id')
            ->where('re.id_persona', '=', $id)
            ->where('rc.estado', '=', 'En Proceso')
            ->count();
    }

    public function getStatusByEntryId($id)
    {
        return DB::table('registro_entrada')
            ->select('id')
            ->where('id', '=', $id)
            ->where('estado', '=', 'Pendiente')->count();
    }

    public function viewFaceCam($id){
        try{
            $search = DB::table('registro_entrada_bio as reb')
                ->join('registro_entrada as re', 're.id', '=', 'reb.fk_registro_entrada')
                ->select('reb.foto_documento as document', 'reb.foto_camera as face', 'reb.foto_huella as finger')
                ->where('re.id', $id);
            if($search->count() > 0) {
                $data = $search->get()[0];
                return response()->json(['face' => $data->face, 'document' => $data->document, 'finger' => $data->finger], 200);
            } else {
                return response()->json(['face' => 'empty', 'document' => 'empty', 'finger' => 'empty'], 200);
            }
            //return File::get(public_path('files/'.$case->path)); // response()->file(public_path('files/'.$case->path));
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Get File Failed!'], 422);
        }
    }
}
