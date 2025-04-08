<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use App\Models\TipoDocumento;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipoDocumentoController extends Controller
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
        $tipoDocumento = TipoDocumento::all();
        //result
        return response()->json($tipoDocumento, 200);
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
        $search = TipoDocumento::where('id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('search') and !empty($request->input('search'))) {            
            $search->orWhere('externalID', 'like', '%' . $request->input('search') . '%');
            $search->orWhere('descripcion', 'like', '%' . $request->input('search') . '%');
        }
        //query
        $tipo = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($tipo as $u) {
            $object = new \stdClass();
            $object->id = $u->id;
            $object->externalID = $u->externalID;
            $object->descripcion = $u->descripcion;
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
            'external' => 'required|string',
            'descripcion' => 'required|string'
        ]);
        //trye
        try {
            //model
            $tipo = new TipoDocumento;
            $tipo->externalID = strtoupper($request->input('external'));
            $tipo->descripcion = strtoupper($request->input('descripcion'));
            //save
            $tipo->save();
            //return successful response
            return response()->json(['tipo_doc' => $tipo, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Tipo Documento Registration failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request 
        $this->validate($request, [
            'external' => 'required|string',
            'descripcion' => 'required|string',
        ]);
        //trye
        try {
            //find
            $tipo = TipoDocumento::findOrFail($id);
            $tipo->update([
                'externalID' => strtoupper($request->input('external')),
                'descripcion' => strtoupper($request->input('descripcion')),
            ]);
            //return successful response
            return response()->json(['tipo_doc' => $tipo, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Tipo Documento Updated Failed!'], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $rol = TipoDocumento::findOrFail($id);
            $rol->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Tipo Documento Deleted Failed!'], 409);
        }
    }

}