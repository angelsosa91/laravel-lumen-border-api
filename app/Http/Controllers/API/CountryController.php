<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class CountryController extends Controller
{
    public function all()
    {
        $paises = Country::all();
        //result
        return response()->json($paises, 200);
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
        $search = Country::where('id', '>', 0);
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
        $paises = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //var_dump($paises); //toSql()
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($paises as $u) {
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
        //try
        try {
            //model
            $pais = new Country;
            $pais->identificador = strtoupper($request->input('identificador'));
            $pais->descripcion = strtoupper($request->input('descripcion'));
            $pais->estado = intval($request->input('estado'));
            //save
            $pais->save();
            //return successful response
            return response()->json(['fronteras' => $pais, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Paises Registration failed'], 409);
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
        //try
        try {
            //find
            $pais = Country::findOrFail($id);
            $pais->update([
                'identificador' => strtoupper($request->input('identificador')),
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['paises' => $pais, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Paises Updated Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //try
        try {
            //find
            $pais = Country::findOrFail($id);
            $pais->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Paises Deleted Failed!'], 409);
        }
    }
}
