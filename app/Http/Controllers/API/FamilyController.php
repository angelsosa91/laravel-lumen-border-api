<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Family;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    //get family by id
    public function showById($id)
    {
        //filters
        $data = DB::table('registro_casos_asistencia_familia')
            ->select('registro_casos_asistencia_familia.*')
            ->where('id_registro_asistencia', '=', $id)->get();
        //array
        $items = array();
        //fetch
        foreach ($data as $d) {
            //push
            $d->nacimiento2 = date("d/m/y", strtotime($d->nacimiento));
            $items[] = $d;
        }
        //return
        return response()->json($items, 200);
    }
    //create
    public function create(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'nombres' => 'required|string',
            'documento' => 'required|string',
            'nacimiento' => 'required',
            'salud' => 'required|string',
            'genero' => 'required',
            'observaciones' => 'required|string'
        ]);
        //try
        try {
            //model
            $family = new Family;
            $family->id_registro_asistencia = $id;
            $family->nombres = strtoupper($request->input('nombres'));
            $family->documento = strtoupper($request->input('documento'));
            $family->nacimiento = $request->input('nacimiento');
            $family->salud = strtoupper($request->input('salud'));
            $family->genero = $request->input('genero');
            $family->observaciones = strtoupper($request->input('observaciones'));
            //save
            $family->save();
            //return successful response
            return response()->json(['Family' => $family, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Family creation failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'nombres' => 'required|string',
            'documento' => 'required|string',
            'nacimiento' => 'required',
            'salud' => 'required|string',
            'genero' => 'required',
            'observaciones' => 'required|string'
        ]);
        //try catch
        try {
            //find alert
            $family = Family::findOrFail($id);
            $family->update([
                'nombres' => strtoupper($request->input('nombres')),
                'documento' => strtoupper($request->input('documento')),
                'nacimiento' => $request->input('nacimiento'),
                'salud' => strtoupper($request->input('salud')),
                'genero' => $request->input('genero'),
                'observaciones' => strtoupper($request->input('observaciones'))
            ]);
            //return successful response
            return response()->json(['Family' => $family, 'message' => 'UPDATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }
    //delete
    public function delete($id)
    {
        //try
        try {
            //find
            $family = Family::findOrFail($id);
            $family->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Family deleted Failed!'], 409);
        }
    }
}
