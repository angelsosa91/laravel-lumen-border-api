<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use  App\Models\Usuarios;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
     /**
     * Instantiate a new UserController instance.
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
     * Get the authenticated User.
     *
     * @return Response
     */
    public function profile()
    {
        //user
        $user = Auth::user();
        //return response()->json(['user' => $user], 200);
        //rol
        $rol = DB::table('roles')
            ->select('roles.*')
            ->where('id', $user->rol)
            ->get();
        //privileges
        $privileges = DB::table('privileges')
            ->join('modules', 'privileges.id_module', '=', 'modules.id')
            ->join('roles', 'privileges.id_rol', '=', 'roles.id')
            ->select('privileges.*', 'roles.role', 'modules.module', 'modules.submodule', 'modules.url', 'modules.icon')
            ->where('id_rol', $user->rol)
            ->get();

        $roles = array(); $prives = array();
        //fetch
        foreach ($rol as $r) {
            array_push($roles, $r);
        }
        //fetch
        foreach ($privileges as $p) {
            array_push($prives, $p);
        }
        //response
        return response()->json(['user' => $user, 'rol' => $roles, 'prives' => $prives], 200);
    }

    /**
     * Get all User.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        //$users = Usuarios::all();
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        $estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //array
        $items = array();
        //filters
        //$search = Usuarios::where('estado', $estado);
        $search = DB::table('usuarios')
            ->join('roles', 'roles.id', '=', 'usuarios.rol')
            ->select('usuarios.*', 'roles.role')
            ->where('estado', $estado);
        //This field uses a LIKE match, handle it separately
        if ($request->has('nombre')) {
            $search->where('nombre', 'like', '%' . $request->input('nombre') . '%');
        }
        if ($request->has('usuario')) {
            $search->where('usuario', 'like', '%' . $request->input('usuario') . '%');
        }
        //query
        $users = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //fetch
        foreach ($users as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
            $object->nombre = $u->nombre;
            $object->usuario = $u->usuario;
            $object->rol = $u->rol;
            $object->role = $u->role;
            $object->estado = ($u->estado == 1) ? 'ACTIVO' : 'INACTIVO';
            $object->created_at = date("d/m/Y H:i", strtotime($u->created_at));
            $object->updated_at = date("d/m/Y H:i", strtotime($u->updated_at));
            //push
            array_push($items, $object);
        }
        return response()->json($items, 200);
    }

    /**
     * Get one user.
     *
     * @return Response
     */
    /*
    public function singleUser(Request $request, $id)
    {
        try {
            $user = Usuarios::findOrFail($id);

            return response()->json(['user' => $user], 200);

        } catch (\Exception $e) {

            return response()->json(['message' => 'user not found!'], 404);
        }

    }
    */
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'nombre' => 'required|string',
            'usuario' => 'required|string',  //unique:usuarios
            'password' => 'required|confirmed',
            'rol' => 'required',
        ]);
        //trye
        try {
            //find
            $userFind = Usuarios::findOrFail($id);
            $userFind->update([
                'nombre' => strtoupper($request->input('nombre')),
                'usuario' => strtolower($request->input('usuario')),
                'rol' => intval($request->input('rol')),
                'password' => app('hash')->make($request->input('password')),
            ]);
            //return successful response
            return response()->json(['user' => $userFind, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage() . 'User Registration Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $userFind = Usuarios::findOrFail($id);
            $userFind->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Deleted Failed!'], 409);
        }
    }
}
