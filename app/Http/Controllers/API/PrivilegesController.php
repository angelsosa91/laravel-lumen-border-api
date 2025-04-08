<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Privileges;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class PrivilegesController extends Controller
{
    public function show(Request $request, $id)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = DB::table('privileges')
            ->join('modules', 'privileges.id_module', '=', 'modules.id')
            ->join('roles', 'privileges.id_rol', '=', 'roles.id')
            ->select('privileges.*', 'roles.role', 'modules.module')
            ->where('id_rol', $id);
        //count
        $count = $search->count();
        //query
        $priv = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($priv as $p) {
            $p->read2 = ($p->read == 1) ? 'SI' : 'NO';
            $p->write2 = ($p->write == 1) ? 'SI' : 'NO';
            $p->update2 = ($p->update == 1) ? 'SI' : 'NO';
            $p->delete2 = ($p->delete == 1) ? 'SI' : 'NO';
            //push
            array_push($items, $p);
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
            'rol' => 'required',
            'module' => 'required',
            'read' => 'required',
            'write' => 'required',
            'update' => 'required',
            'delete' => 'required',
        ]);
        //trye
        try {
            //model
            $priv = new Privileges;
            $priv->id_rol = intval($request->input('rol'));
            $priv->id_module = intval($request->input('module'));
            $priv->read = intval($request->input('read'));
            $priv->write = intval($request->input('write'));
            $priv->update = intval($request->input('update'));
            $priv->delete = intval($request->input('delete'));
            $priv->access = 1;
            //save
            $priv->save();
            //return successful response
            return response()->json(['priv' => $priv, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Privileges Registration Failed!'], 409); //$e->getMessage()
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'rol' => 'required',
            'module' => 'required',
            'read' => 'required',
            'write' => 'required',
            'update' => 'required',
            'delete' => 'required',
        ]);
        //trye
        try {
            //find
            $priv = Privileges::findOrFail($id);
            $priv->update([
                'id_rol' => intval($request->input('rol')),
                'id_module' => intval($request->input('module')),
                'read' => intval($request->input('read')),
                'write' => intval($request->input('write')),
                'update' => intval($request->input('update')),
                'delete' => intval($request->input('delete')),
            ]);
            //return successful response
            return response()->json(['priv' => $priv, 'message' => 'UPDATED'], 201);
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
            $priv = Privileges::findOrFail($id);
            $priv->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Privileges Deleted Failed!'], 409);
        }
    }
}
