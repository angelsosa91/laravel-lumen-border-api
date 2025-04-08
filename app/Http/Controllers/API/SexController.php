<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sex;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SexController extends Controller
{
    public function show()
    {
        $sex = Sex::all();
        //result
        return response()->json($sex, 200);
    }
}
