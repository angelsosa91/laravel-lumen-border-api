<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Migrations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrationsController extends Controller
{
    public function printOne(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = DB::table('movimiento_migratorio')
            ->join('data_tipo_documentos', 'data_tipo_documentos.externalID', '=', 'movimiento_migratorio.tipo_documento')
            ->select('movimiento_migratorio.id', 'nombres', 'apellidos', 'fecha_nacimiento', 'documento_numero', 'identidad_numero', 'sexo', 'tipo_documento', 'pais_emision', 'nacionalidad', 'fecha_expiracion', 'foto_documento', 'foto_camera', 'movimiento', 'fecha_registro', 'permitido', 'sincronizado', 'update_Date', 'UUID', 'usuario', 'foto_huella', 'nombre_equipo', 'nombre_frontera', 'direccion', 'motivo_viaje', 'dias_estadia', 'procedencia_destino', 'contacto', 'observacion', 'data_tipo_documentos.descripcion');
            //->where('movimiento_migratorio.id', '>', 0);
        //count
        $count = $search->count();
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
        if ($request->has('identidad') and !empty($request->input('identidad'))) {
            $search->orWhere('identidad_numero', 'like', '%' . $request->input('identidad') . '%');
        }
        if ($request->has('genero') and !empty($request->input('genero'))) {
            $search->where('sexo', $request->input('genero'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween('fecha_registro', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('movimiento') and !empty($request->input('movimiento'))) {
            $search->where('movimiento', $request->input('movimiento'));
        }
        if ($request->has('id') and !empty($request->input('id'))) {
            $search->where('movimiento_migratorio.id', $request->input('id'));
        }
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
		//echo $mov; exit;
		//dd(\DB::getQueryLog()); exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
	//imprimir detalle
	public function printTwo(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
		$search = DB::table('movimiento_migratorio')
            ->select('id', 'movimiento', 'fecha_registro', 'nombre_equipo', 'nombre_frontera', 'fecha_expiracion', 'fecha_nacimiento');
            //->where('id', '>', 0);
        //count
        $count = $search->count();
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
        if ($request->has('identidad') and !empty($request->input('identidad'))) {
            $search->orWhere('identidad_numero', 'like', '%' . $request->input('identidad') . '%');
        }
        if ($request->has('genero') and !empty($request->input('genero'))) {
            $search->where('sexo', $request->input('genero'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween('fecha_registro', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('movimiento') and !empty($request->input('movimiento'))) {
            $search->where('movimiento', $request->input('movimiento'));
        }
        if ($request->has('id') and !empty($request->input('id'))) {
            $search->where('movimiento_migratorio.id', $request->input('id'));
        }
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
		//echo $mov; exit;
		//echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
	//select without docs
	public function data(Request $request)
    {
		//var_dump($request->all()); exit;
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "movimiento_migratorio.id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        //$search = MovimientoMigratorio::where('id', '>', 0);
        $search = DB::table('movimiento_migratorio')
			//documento identidad por pais
			//if ($request->has('identidad') and !empty($request->input('identidad'))) {}
			//documento BIH
			/*if ($request->has('documento') and !empty($request->input('documento'))) {
				->join('data_tipo_documentos', 'data_tipo_documentos.externalID', '=', 'movimiento_migratorio.tipo_documento')
			}*/
            ->join('data_tipo_documentos', 'data_tipo_documentos.externalID', '=', 'movimiento_migratorio.tipo_documento')
            ->select('movimiento_migratorio.id', 'nombres', 'apellidos', 'fecha_nacimiento', 'documento_numero', 'identidad_numero', 'sincronizado', 'update_Date', 'UUID', 'tipo_documento', 'sexo', 'pais_emision', 'nacionalidad', 'fecha_expiracion', 'movimiento', 'fecha_registro', 'data_tipo_documentos.descripcion');
            //->where('movimiento_migratorio.id', '>', 0);
        //count
        $count = $search->count();
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
        if ($request->has('identidad') and !empty($request->input('identidad'))) {
            $search->orWhere('identidad_numero', 'like', '%' . $request->input('identidad') . '%');
        }
        if ($request->has('genero') and !empty($request->input('genero'))) {
            $search->where('sexo', $request->input('genero'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {
            $search->whereBetween('fecha_registro', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('movimiento') and !empty($request->input('movimiento'))) {
            $search->where('movimiento', $request->input('movimiento'));
        }
        if ($request->has('id') and !empty($request->input('id'))) {
            $search->where('movimiento_migratorio.id', $request->input('id'));
        }
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
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
	//select with docs
	public function docs(Request $request, $id)
    {
        //filters
        $search = DB::table('movimiento_migratorio')
            ->join('data_tipo_documentos', 'data_tipo_documentos.externalID', '=', 'movimiento_migratorio.tipo_documento')
            ->select('movimiento_migratorio.foto_documento', 'movimiento_migratorio.foto_camera', 'movimiento_migratorio.foto_huella', 'data_tipo_documentos.descripcion')
            ->where('movimiento_migratorio.id', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            //$m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            //$m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            //$m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
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
    public function create(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'nombre' => 'required|string',
            'usuario' => 'required|string|unique:usuarios',
            'password' => 'required|confirmed',
        ]);
        //trye
        try {
            //model
            $mov = new Migrations;
            $mov->nombre = strtoupper($request->input('nombre'));
            $mov->usuario = strtolower($request->input('usuario'));
            $mov->password = app('hash')->make($request->input('password'));
            $mov->uuid = Str::uuid()->toString();
            //save
            $mov->save();
            //return successful response
            return response()->json(['mov' => $mov, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'nombre' => 'required|string',
            'usuario' => 'required|string|unique:usuarios',
            'password' => 'required|confirmed',
        ]);
        //trye
        try {
            //find
            $movFind = Migrations::findOrFail($id);
            $movFind->update([
                'nombre' => strtoupper($request->input('nombre')),
                'usuario' => strtolower($request->input('usuario')),
                'password' => app('hash')->make($request->input('password')),
            ]);
            //return successful response
            return response()->json(['user' => $movFind, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $movFind = Migrations::findOrFail($id);
            $movFind->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Deleted Failed!'], 409);
        }
    }
}
