<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use App\Models\Fronteras;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FronterasController extends Controller
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

    public function all()
    {
        $fronteras = Fronteras::all();
        //result
        return response()->json($fronteras, 200);
    }
    //get order by rows
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
        $search = Fronteras::where('id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('estado')) {  //and !empty($request->input('estado'))
            $search->where('estado', intval($request->input('estado')));
        }
        if ($request->has('search') and !empty($request->input('search'))) {            
            $search->where('identificador', 'like', '%' . $request->input('search') . '%');
            $search->orWhere('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $fronteras = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //var_dump($fronteras); toSql()
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($fronteras as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
            $object->identificador = $u->identificador;
            $object->descripcion = $u->descripcion;
            $object->estado = $u->estado;
            $object->status = ($u->estado == 1) ? 'ACTIVO' : 'INACTIVO';
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
            'identificador' => 'required|string',
            'descripcion' => 'required|string',
            'estado' => 'required'
        ]);
        //trye
        try {
            //model
            $front = new Fronteras;
            $front->identificador = strtoupper($request->input('identificador'));
            $front->descripcion = strtoupper($request->input('descripcion'));
            $front->estado = intval($request->input('estado'));
            //save
            $front->save();
            //return successful response
            return response()->json(['fronteras' => $front, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Fronteras Registration failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request 
        $this->validate($request, [
            'identificador' => 'required|string',
            'descripcion' => 'required|string',
            'estado' => 'required'
        ]);
        //trye
        try {
            //find
            $front = Fronteras::findOrFail($id);
            $front->update([
                'identificador' => strtoupper($request->input('identificador')),
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['fronteras' => $front, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Fronteras Updated Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $front = Fronteras::findOrFail($id);
            $front->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Fronteras Deleted Failed!'], 409);
        }
    }

}