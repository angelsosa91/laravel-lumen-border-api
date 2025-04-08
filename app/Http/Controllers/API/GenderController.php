<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Gender;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GenderController extends Controller
{
    public function show()
    {
        $gender = Gender::all();
        //result
        return response()->json($gender, 200);
    }

    public function showStatus()
    {
        $status = Status::all();
        //result
        return response()->json($status, 200);
    }
}
