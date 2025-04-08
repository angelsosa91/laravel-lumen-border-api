<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Diagnosis;
use App\Models\Potential;
use App\Models\Folio;

class TypesController extends Controller
{

    public function showAllDiagnosis()
    {
        $diagnosis = Diagnosis::all();
        //result
        return response()->json($diagnosis, 200);
    }

    public function showAllPotential()
    {
        $potential = Potential::all();
        //result
        return response()->json($potential, 200);
    }

    public function showAllFolio()
    {
        $folio = Folio::all();
        //result
        return response()->json($folio, 200);
    }

    public function showDiagnosis(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = Diagnosis::where('id', '>', 0);
        //count
        $count = $search->count();
        //filter
        if ($request->has('search') and !empty($request->input('search'))) {
            $search->where('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $diagnosis = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($diagnosis as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
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

    public function showPotential(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = Potential::where('id', '>', 0);
        //count
        $count = $search->count();
        //filter
        if ($request->has('search') and !empty($request->input('search'))) {
            $search->where('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $potential = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($potential as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
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

    public function showFolio(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = Folio::where('id', '>', 0);
        //count
        $count = $search->count();
        //filter
        if ($request->has('search') and !empty($request->input('search'))) {
            $search->where('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $potential = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($potential as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
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

    public function createDiagnosis(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $diagnosis = new Diagnosis;
        $diagnosis->descripcion = strtoupper($request->input('descripcion'));
        $diagnosis->estado = intval($request->input('estado'));
        $diagnosis->save();
        return response()->json($diagnosis, 200);
    }

    public function createPotential(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $potential = new Potential;
        $potential->descripcion = strtoupper($request->input('descripcion'));
        $potential->estado = intval($request->input('estado'));
        $potential->save();
        return response()->json($potential, 200);
    }

    public function createFolio(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        $folio = new Folio;
        $folio->descripcion = strtoupper($request->input('descripcion'));
        $folio->estado = intval($request->input('estado'));
        $folio->save();
        return response()->json($folio, 200);
    }

    public function updateDiagnosis(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //trye
        try {
            //find
            $diagnosis = Diagnosis::findOrFail($id);
            $diagnosis->update([
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['diagnosis' => $diagnosis, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Diagnosis Updated Failed!'], 409);
        }
    }

    public function updatePotential(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //trye
        try {
            //find
            $potential = Potential::findOrFail($id);
            $potential->update([
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['potential' => $potential, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Potential Updated Failed!'], 409);
        }
    }

    public function updateFolio(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'descripcion' => 'required',
            'estado' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());
        }

        //trye
        try {
            //find
            $folio = Folio::findOrFail($id);
            $folio->update([
                'descripcion' => strtoupper($request->input('descripcion')),
                'estado' => intval($request->input('estado')),
            ]);
            //return successful response
            return response()->json(['folio' => $folio, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Folio Updated Failed!'], 409);
        }
    }
}
