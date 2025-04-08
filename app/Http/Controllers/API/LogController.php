<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\LogJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LogController extends Controller
{
    //create
    public function create(Request $request, $uuid)
    {
        $input = $request->all();
        //try
        try {
            //model log
            $log = new LogJson;
            $log->json = json_encode($input, JSON_UNESCAPED_UNICODE);
            $log->mobil_uuid = $uuid;
            $log->mobil_nombre = 'Matriz';
            $log->save();
            //return successful response
            return response()->json(['log' => $log, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => $e->getMessage()], 409);//'Log Created Failed!'
        }
    }
}
