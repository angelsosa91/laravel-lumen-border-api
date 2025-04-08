<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Border;
use App\Http\Resources\Border as BorderResource;

class BorderController extends Controller
{
    /*
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    */
    public function all()
    {
        $borders = Border::all();
        return response()->json($borders, 200);
        //return $this->sendResponse(BorderResource::collection($borders), 'Borders fetched.');
    }

    public function allBySync()
    {
        $borders = DB::table('data_fronteras')
            ->join('sincronizacion_frontera', 'data_fronteras.descripcion', '=', 'sincronizacion_frontera.nombre_frontera')
            ->select('data_fronteras.*')
            ->groupBy('id')->get();
        return response()->json($borders, 200);
        //return $this->sendResponse(BorderResource::collection($borders), 'Borders fetched.');
    }

    public function create(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'identificador' => 'required',
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $border = Border::create($input);
        return response()->json($border, 200);
        //return $this->sendResponse(new BorderResource($border), 'Border created.');
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
        $search = Border::where('id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        /*if ($request->has('estado')) {  //and !empty($request->input('estado'))
            $search->where('estado', intval($request->input('estado')));
        }*/
        if ($request->has('search') and !empty($request->input('search'))) {
            $search->where('identificador', 'like', '%' . $request->input('search') . '%');
            $search->orWhere('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $fronteras = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //var_dump($paises); //toSql()
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
    /*
    public function show($id)
    {
        $border = Border::find($id);
        if (is_null($border)) {
            return $this->sendError('Border does not exist.');
        }
        return response()->json($border, 200);
        //return $this->sendResponse(new BorderResource($border), 'Border fetched.');
    }
    */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'identificador' => 'required',
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //trye
        try {
            //find
            $fontera = Border::findOrFail($id);
            $fontera->update([
                'identificador' => strtoupper($request->input('identificador')),
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['fronteras' => $fontera, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Borders Updated Failed!'], 409);
        }

        //$border->identificador = $input['identificador'];
        //$border->descripcion = $input['descripcion'];
        //$border->estado = $input['estado'];
        //$border->save();

        //return response()->json($border, 200);
        //return $this->sendResponse(new BorderResource($border), 'Post updated.');
    }

    public function destroy(Border $border)
    {
        $border->delete();
        return $this->sendResponse([], 'Border deleted.');
    }
}
