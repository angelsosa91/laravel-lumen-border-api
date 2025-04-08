<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use  App\Models\Genero;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneroController extends Controller
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
    public function show()
    {
        $genero = Genero::all();
        //result
        return response()->json($genero, 200);
    }
    
}