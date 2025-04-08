<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Suspect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuspectController extends Controller
{
    public function show(Request $request)
    {
        //$users = Usuarios::all();
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        $key = ($request->has('key')) ? intval($request->input('key')) : 1;
        $offset = ($page-1)*$rows;
        //array
        $result = array(); $items = array();
        //filters
        //$search = PersonasSospechosas::where('id', '>', 0);
        $search = DB::table('personas_sospechosas')
            ->join('data_tipo_documentos', 'data_tipo_documentos.externalID', '=', 'personas_sospechosas.tipo_documento')
            ->select('personas_sospechosas.*', 'data_tipo_documentos.descripcion')
            ->where('personas_sospechosas.id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('nombres') and !empty($request->input('nombres'))) {
            $search->where('nombres', 'like', '%' . $request->input('nombres') . '%');
        }
        if ($request->has('apellidos')  and !empty($request->input('apellidos'))) {
            $search->where('apellidos', 'like', '%' . $request->input('apellidos') . '%');
        }
        if ($request->has('numero_documento')  and !empty($request->input('numero_documento'))) {
            $search->orWhere('numero_documento', 'like', '%' . $request->input('numero_documento') . '%');
        }
        if ($request->has('sexo')  and !empty($request->input('sexo'))) {
            $search->where('sexo', $request->input('sexo'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween(DB::raw('DATE(created_at)'), [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        //query
        $alerts = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql
        //var_dump($alerts);
        //exit();
        //fetch
        foreach ($alerts as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
            $object->nombres = $u->nombres;
            $object->apellidos = $u->apellidos;
            $object->fecha_nac = $u->fecha_nacimiento;
            $object->fecha_nacimiento = date("d/m/Y", strtotime($u->fecha_nacimiento));
            $object->nacionalidad = $u->nacionalidad;
            $object->tipo_documento = $u->tipo_documento;
            $object->sexo = $u->sexo;
            $object->numero_documento = $u->numero_documento;
            $object->numero_personal = $u->numero_personal;
            $object->documento_fuente = $u->documento_fuente;
            $object->quien_configuro = $u->quien_configuro;
            $object->informacion = $u->informacion;
            $object->accion_requerida = $u->accion_requerida;
            $object->descripcion = $u->descripcion;
            $object->fecha_can = $u->fecha_cancelacion;
            $object->fecha_cancelacion = date("d/m/Y", strtotime($u->fecha_cancelacion));
            $object->created_at = date("d/m/Y H:i", strtotime($u->created_at));
            $object->updated_at = date("d/m/Y H:i", strtotime($u->updated_at));
            //push
            array_push($items, $object);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
    //create
    public function create(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'fecha_nacimiento' => 'required',
            'nacionalidad' => 'required|string',
            'tipo_documento' => 'required|string',
            'sexo' => 'required|string',
            'numero_documento' => 'required',
            'numero_personal' => 'required',
            'documento_fuente' => 'required',
            'quien_configuro' => 'required',
            'informacion' => 'required',
            'accion_requerida' => 'required'
            //'fecha_cancelacion' => 'required|date'
        ]);
        //trye
        try {
            //model
            $alertas = new Suspect;
            $alertas->nombres = strtoupper($request->input('nombres'));
            $alertas->apellidos = strtoupper($request->input('apellidos'));
            $alertas->fecha_nacimiento = $request->input('fecha_nacimiento');
            $alertas->nacionalidad = strtoupper($request->input('nacionalidad'));
            $alertas->tipo_documento = $request->input('tipo_documento');
            $alertas->sexo = $request->input('sexo');
            $alertas->numero_documento = $request->input('numero_documento');
            $alertas->numero_personal = $request->input('numero_personal');
            $alertas->documento_fuente = strtoupper($request->input('documento_fuente'));
            $alertas->quien_configuro = strtoupper($request->input('quien_configuro'));
            $alertas->informacion = strtoupper($request->input('informacion'));
            $alertas->accion_requerida = strtoupper($request->input('accion_requerida'));
            $alertas->fecha_cancelacion = ($request->has('fecha_cancelacion') and !empty($request->input('fecha_cancelacion'))) ? $request->input('fecha_cancelacion') : null;
            //save
            $alertas->save();
            //return successful response
            return response()->json(['alertas' => $alertas, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage()], 409);//'Personas Sospechosas Registration failed'
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'nacionalidad' => 'required|string',
            'tipo_documento' => 'required|string',
            'sexo' => 'required|string',
            'numero_documento' => 'required',
            'numero_personal' => 'required',
            'documento_fuente' => 'required',
            'quien_configuro' => 'required',
            'informacion' => 'required',
            'accion_requerida' => 'required'
            //'fecha_cancelacion' => 'required|date'
        ]);
        //trye
        try {
            //find
            $alertas = Suspect::findOrFail($id);
            $alertas->update([
                'nombres' => strtoupper($request->input('nombres')),
                'apellidos' => strtoupper($request->input('apellidos')),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'nacionalidad' => strtoupper($request->input('nacionalidad')),
                'tipo_documento' => $request->input('tipo_documento'),
                'sexo' => $request->input('sexo'),
                'numero_documento' => $request->input('numero_documento'),
                'numero_personal' => $request->input('numero_personal'),
                'documento_fuente' => strtoupper($request->input('documento_fuente')),
                'quien_configuro' => strtoupper($request->input('quien_configuro')),
                'informacion' => strtoupper($request->input('informacion')),
                'accion_requerida' => strtoupper($request->input('accion_requerida')),
                'fecha_cancelacion' => ($request->has('fecha_cancelacion') and !empty($request->input('fecha_cancelacion'))) ? $request->input('fecha_cancelacion') : null
            ]);
            //return successful response
            return response()->json(['user' => $alertas, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage() . 'Personas Sospechosas Updated Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $userFind = Suspect::findOrFail($id);
            $userFind->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Personas Sospechosas Deleted Failed!'], 409);
        }
    }
}
