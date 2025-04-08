<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use  App\Models\Modules;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ModulesController extends Controller
{
     /**
     * Instantiate a new MovimientoMigratorioController instance.
     *
     * @return void
     */
    /*/
    public function __construct()
    {
        $this->middleware('auth');
    }
    */
    /**
     * Get all movements.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = Modules::where('id', '>', 0);
        //This field uses a LIKE match, handle it separately
        if ($request->has('role')) {            
            $search->where('role', 'like', '%' . $request->input('role') . '%');
        } 
        if ($request->has('status')) {            
            $search->where('apellidos', 'like', '%' . $request->input('apellido') . '%');
        }
        if ($request->has('documento')) {            
            $search->where('documento_numero', 'like', '%' . $request->input('documento') . '%');
        }
        if ($request->has('identidad')) {            
            $search->where('identidad_numero', 'like', '%' . $request->input('identidad') . '%');
        }
        if ($request->has('genero')) {            
            $search->where('sexo', $request->input('genero'));
        }
        if ($request->has('nacimiento_desde') and $request->has('nacimiento_hasta') and !empty($request->input('nacimiento_desde')) and !empty($request->input('nacimiento_hasta'))) {            
            $search->whereBetween('fecha_nacimiento', [$request->input('nacimiento_desde'), $request->input('nacimiento_hasta')]);
        }
        if ($request->has('registro_desde') and $request->has('registro_hasta')  and !empty($request->input('registro_desde')) and !empty($request->input('registro_hasta'))) {            
            $search->whereBetween('fecha_registro', [$request->input('registro_desde'), $request->input('registro_hasta')]);
        }
        if ($request->has('status')) {            
            $search->where('status', $request->input('status'));
        }
        //query
        $mov = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $items = array();
        //fetch
        foreach ($mov as $m) {
            $m->fecha_expiracion = date("d/m/y", strtotime($m->fecha_expiracion));
            $m->fecha_registro = date("d/m/y H:i", strtotime($m->fecha_registro));
            $m->fecha_nacimiento = date("d/m/y", strtotime($m->fecha_nacimiento));
            //push
            array_push($items, $m);
        }
        //result
        return response()->json($items, 200);
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
            $mov = new Modules;
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
            $movFind = Modules::findOrFail($id);
            $movFind->update([
                'nombre' => strtoupper($request->input('nombre')),
                'usuario' => strtolower($request->input('usuario')),
                'password' => app('hash')->make($request->input('password')),
            ]);
            //return successful response
            return response()->json(['user' => $userFind, 'message' => 'UPDATED'], 201);
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
            $movFind = Modules::findOrFail($id);
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