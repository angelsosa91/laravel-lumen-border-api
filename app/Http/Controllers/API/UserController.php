<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\User as UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /*public function __construct()
    {
        $this->middleware('auth:api');
    }
    */
    /*public function index()
    {
        $users = User::all();
        return response()->json(UserResource::collection($users), 200);
        //return $this->sendResponse(UserResource::collection($users), 'Users fetched.');
    }*/

    public function profile()
    {
        //user
        $user = Auth::user();
        //rol
        $rol = DB::table('roles')
            ->select('roles.*')
            ->where('id', $user->rol)
            ->get();
        //privileges
        $module = DB::table('privileges')
            ->join('modules', 'privileges.id_module', '=', 'modules.id')
            ->join('roles', 'privileges.id_rol', '=', 'roles.id')
            ->select('roles.role', 'modules.module', 'modules.submodule', 'modules.url', 'modules.icon') //privileges.*
            ->where('id_rol', $user->rol)
            ->where('menu', 1)
            ->orderBy('order')
            ->get();

        $roles = array(); $modules = array();
        //fetch
        foreach ($rol as $r) {
            $roles[] = $r;
        }
        //fetch
        foreach ($module as $p) {
            $modules[] = $p;
        }

        //response
        return response()->json(['user' => $user, 'roles' => $roles, 'modules' => $module], 200);
    }

    public function privileges($module)
    {
        //user
        $user = Auth::user();
        //rol
        $rolId = DB::table('users')
            ->select('rol')
            ->where('id', $user->id)
            ->get()[0]->rol;
        //module
        $moduleId = DB::table('modules')
            ->select('id')
            ->where('url', trim(base64_decode($module)))
            ->get()[0]->id;
        //privileges
        $permission = DB::table('privileges')
            ->select('access', 'status', 'read', 'write', 'update', 'delete')
            ->where('id_rol', $rolId)
            ->where('id_module', $moduleId)
            ->get();

        $permissions = array();
        //fetch
        foreach ($permission as $p) {
            $permissions[] = $p;
        }
        //response
        return response()->json(['permission' => $permissions], 200);
    }

    public function show(Request $request)
    {
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
        $search = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.rol')
            ->select('users.*', 'roles.role')
            ->addSelect(DB::raw('(SELECT name FROM users us WHERE us.id = users.supervisor) as supername'))
            ->where('estado', $estado);
        //This field uses a LIKE match, handle it separately
        if ($request->has('name') and !empty($request->has('name'))) {
            $search->where('name', 'like', '%' . $request->input('name') . '%');
        }
        if ($request->has('email') and !empty($request->has('email'))) {
            $search->where('email', 'like', '%' . $request->input('email') . '%');
        }
        //query
        $users = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //fetch
        foreach ($users as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
            $object->nombre = strtoupper($u->name);
            $object->usuario = $u->username;
            $object->email = $u->email;
            $object->rol = $u->rol;
            $object->role = $u->role;
            $object->supervisor = $u->supervisor;
            $object->supername = ($u->supervisor == 0) ? 'NO TIENE' : $u->supername;
            $object->estado = ($u->estado == 1) ? 'ACTIVO' : 'INACTIVO';
            $object->created_at = date("d/m/Y H:i", strtotime($u->created_at));
            $object->updated_at = date("d/m/Y H:i", strtotime($u->updated_at));
            //push
            array_push($items, $object);
        }
        return response()->json($items, 200);
    }

    public function showByRole()
    {

        $users = DB::table('users')
            ->select('id', 'name')
            ->where('estado', 1)
            ->where('rol', 2)
            ->get();

        return response()->json($users, 200);
    }
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
    //create
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'username' => 'email|required|unique:users',
            'password' => 'required|confirmed',
            'rol' => 'required'
        ]);

        $validatedData['password'] = bcrypt($request->password);

        $user = User::create([
            'name' => strtoupper($request->input('name')),
            'username' => strtolower($request->input('username')),
            'email' => strtolower($request->input('email')),
            'rol' => intval($request->input('rol')),
            'supervisor' => intval($request->input('supervisor')),
            'password' => bcrypt($request->password),
        ]);
        //app('hash')->make($request->input('password')),

        $accessToken = $user->createToken('authToken')->accessToken;

        return response([ 'user' => $user, 'access_token' => $accessToken]);
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required|string',
            'username' => 'required|string',  //unique:usuarios
            'email' => 'required|string',
            'password' => 'required|confirmed',
            'rol' => 'required'
        ]);
        //try
        try {
            //find
            $userFind = User::findOrFail($id);
            $userFind->update([
                'name' => strtoupper($request->input('name')),
                'username' => strtolower($request->input('username')),
                'email' => strtolower($request->input('email')),
                'rol' => intval($request->input('rol')),
                'supervisor' => intval($request->input('supervisor')),
                'password' => bcrypt($request->password),
            ]);
            //app('hash')->make($request->input('password')),
            //return successful response
            return response()->json(['user' => $userFind, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage() . 'User updated failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //try
        try {
            //find
            $userFind = User::findOrFail($id);
            //$userFind->delete();
            $userFind->update([
                'estado' => 0,
            ]);
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Deleted Failed!'], 409);
        }
    }

    public function validateToken(Request $request)
    {
        if (Auth::guard('api')->check()) {
            return response()->json(['success' => true, 'message' => 'alive'], 200);
        }
        // return general data
        return response(['message' => 'Unauthenticated user'], 401);
    }
}
