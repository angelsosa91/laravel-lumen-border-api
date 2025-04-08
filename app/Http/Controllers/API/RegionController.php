<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class RegionController extends Controller
{
    public function all()
    {
        $regions = Region::orderBy("region", "asc")->get(); //all()
        //result
        return response()->json($regions, 200);
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
        $search = Region::where('id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        /*if ($request->has('estado')) {  //and !empty($request->input('estado'))
            $search->where('estado', intval($request->input('estado')));
        }*/
        if ($request->has('search') and !empty($request->input('search'))) {
            $search->where('region', 'like', '%' . $request->input('search') . '%');
            //$search->orWhere('descripcion', 'like', '%' . $request->input('search') . '%');
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
            $object->region = $u->region;
            //$object->descripcion = $u->descripcion;
            //$object->estado = $u->estado;
            //$object->status = ($u->estado == 1) ? 'ACTIVO' : 'INACTIVO';
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
            'region' => 'required|string'
        ]);
        //trye
        try {
            //model
            $region = new Region;
            $region->region = strtoupper($request->input('region'));
            //$pais->descripcion = strtoupper($request->input('descripcion'));
            //$pais->estado = intval($request->input('estado'));
            //save
            $region->save();
            //return successful response
            return response()->json(['fronteras' => $region, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Region Registration failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'region' => 'required|string'
        ]);
        //trye
        try {
            //find
            $region = Region::findOrFail($id);
            $region->update([
                'region' => strtoupper($request->input('region'))
            ]);
            //return successful response
            return response()->json(['paises' => $region, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Region Updated Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $pais = Region::findOrFail($id);
            $pais->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Region Deleted Failed!'], 409);
        }
    }
}
