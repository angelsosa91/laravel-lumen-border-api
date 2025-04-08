<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolController extends Controller
{
    public function all()
    {
        $roles = Rol::all();
        //result
        return response()->json($roles, 200);
    }

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
        $search = DB::table('roles')
            ->join('modules', 'roles.location', '=', 'modules.url')
            ->select('roles.*', 'modules.module');
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('role') and !empty($request->has('role'))) {
            $search->where('role', 'like', '%' . $request->input('role') . '%');
        }
        if ($request->has('status') and !empty($request->has('status'))) {
            $search->where('status', $request->input('status'));
        }
        //array
        $result = array(); $items = array();
        //query
        $items = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
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
            'role' => 'required|string',
            'location' => 'required|string',
            //'location' => 'required|confirmed',
        ]);
        //trye
        try {
            //model
            $rol = new Rol;
            $rol->role = strtoupper($request->input('role'));
            $rol->location = strtolower($request->input('location'));
            //save
            $rol->save();
            //return successful response
            return response()->json(['rol' => $rol, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Roles Registration failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'role' => 'required|string',
            'location' => 'required|string',
        ]);
        //trye
        try {
            //find
            $rol = Rol::findOrFail($id);
            $rol->update([
                'role' => strtoupper($request->input('role')),
                'location' => strtolower($request->input('location')),
            ]);
            //return successful response
            return response()->json(['rol' => $rol, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Roles Registration Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $rol = Rol::findOrFail($id);
            $rol->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Roles Deleted Failed!'], 409);
        }
    }
}
