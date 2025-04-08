<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Migrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::all();
        //result
        return response()->json($alerts, 200);
    }

    public function countByIn()
    {
        $alerts = DB::table('movimiento_alertas')->where('estado', '=', '1')->count();
        //result
        return response()->json(["rows" => $alerts], 200);
    }

    public function countByOut()
    {
        $alerts = DB::table('movimiento_alertas')->where('estado', '=', '2')->count();
        //result
        return response()->json(["rows" => $alerts], 200);
    }
    //get order by rows
    public function show(Request $request)
    {
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "movimiento_alertas.fecha_registro";
        $order = ($request->has('order')) ? strval($request->input('order')) : "desc";
        //$status = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $key = ($request->has('key')) ? intval(base64_decode($request->input('key'))) : 0;
        $offset = ($page-1)*$rows;
        //filters
        $search = DB::table('movimiento_alertas')
            ->join('movimiento_migratorio', 'movimiento_migratorio.id', '=', 'movimiento_alertas.fk_movimiento_migratorio')
            ->join('personas_sospechosas', 'personas_sospechosas.id', '=', 'movimiento_alertas.fk_personas_sospechosas')
            ->select('movimiento_alertas.*', 'movimiento_migratorio.*', 'personas_sospechosas.*');
        //This field uses a LIKE match, handle it separately
        if ($key > 0) {
            $search->where('movimiento_alertas.id', '=', $key);
        } else {
            $search->where('movimiento_alertas.estado', '=', 1);
        }
        //count
        $count = $search->count();
        //query
        $data = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get();
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($data as $d) {
            /*
            $object = new \stdClass();
            $object->id = $u->id;
            $object->identificador = $u->identificador;
            $object->descripcion = $u->descripcion;
            $object->estado = $u->estado;
            $object->status = ($u->estado == 1) ? 'ACTIVO' : 'INACTIVO';
            $object->created_at = date("d/m/Y H:i", strtotime($u->created_at));
            $object->updated_at = date("d/m/Y H:i", strtotime($u->updated_at));
            */
            //push
            array_push($items, $d);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }
    //get order by rows
    public function countByGroup()
    {
        //filters
        $data = DB::table('movimiento_alertas')
            ->join('movimiento_migratorio', 'movimiento_migratorio.id', '=', 'movimiento_alertas.fk_movimiento_migratorio')
            ->join('personas_sospechosas', 'personas_sospechosas.id', '=', 'movimiento_alertas.fk_personas_sospechosas')
            ->selectRaw('count(*) as qty, movimiento_alertas.estado')
            ->groupBy('movimiento_alertas.estado')->get();
        //return
        return response()->json($data, 200);
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
            $pais = new Alert;
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
            'supervisor_ip' => 'required|string',
            'supervisor_nota' => 'required|string',
            'accion_tomada' => 'required',
            'supervisor_usuario' => 'required',
            'movement' => 'required',
        ]);
        //try catch
        try {
            //find alert
            $alert = Alert::findOrFail($id);
            $alert->update([
                'supervisor_ip' => strtoupper($request->input('supervisor_ip')),
                'supervisor_nota' => strtoupper($request->input('supervisor_nota')),
                'accion_tomada' => intval($request->input('accion_tomada')),
                'supervisor_usuario' => intval($request->input('supervisor_usuario')),
                'estado' => 2,
            ]);
            //var
            $enable = (intval($request->input('accion_tomada')) == 1) ? 1 : 0;
            //find movement
            $migration = Migrations::findOrFail(intval($request->input('movement')));
            $migration->update([
                'permitido' => strtoupper($enable),
                'update_Date' => Carbon::now(),
            ]);
            //return successful response
            return response()->json(['alerta' => $alert, 'migration' => $migration, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //trye
        try {
            //find
            $pais = Alert::findOrFail($id);
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
