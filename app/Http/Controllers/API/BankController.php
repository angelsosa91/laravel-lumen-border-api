<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class BankController extends Controller
{
    //get family by id
    public function showById($id)
    {
        //filters
        $data = DB::table('registro_casos_banco')
            ->select('*')
            ->where('fk_registro_casos', $id)->get();
        //array
        $items = array();
        //fetch
        foreach ($data as $d) {
            //push
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
            'banco' => 'required|string',
            'cuenta' => 'required|string'
        ]);
        //try
        try {
            //model
            $bank = new Bank;
            $bank->fk_registro_casos = $id;
            $bank->banco = strtoupper($request->input('banco'));
            $bank->cuenta = strtoupper($request->input('cuenta'));
            //save
            $bank->save();
            //return successful response
            return response()->json(['Bank' => $bank, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Bank creation failed'], 409);
        }
    }
    //update
    public function update(Request $request, $id)
    {
        //validate incoming request
        $this->validate($request, [
            'banco' => 'required|string',
            'cuenta' => 'required|string'
        ]);
        //try catch
        try {
            //find alert
            $bank = Bank::findOrFail($id);
            $bank->update([
                'banco' => strtoupper($request->input('banco')),
                'cuenta' => strtoupper($request->input('cuenta'))
            ]);
            //return successful response
            return response()->json(['Bank' => $bank, 'message' => 'UPDATED'], 201);
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
            $bank = Bank::findOrFail($id);
            $bank->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Bank deleted Failed!'], 409);
        }
    }
}
