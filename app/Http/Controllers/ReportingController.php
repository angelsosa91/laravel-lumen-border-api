<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use App\Models\TipoDocumento;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
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
    //get order by rows
    public function showByDocument() //Request $request
    {
        //filters
        $query = DB::table('movimiento_migratorio')
            ->join('data_tipo_documentos', 'movimiento_migratorio.tipo_documento', '=', 'data_tipo_documentos.externalID')
            ->select(DB::raw('count(*) as qty, externalID'))
            ->groupBy('externalID')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByCountry() //Request $request
    {
        //filters
        $query = DB::table('movimiento_migratorio')
            ->select(DB::raw('count(*) as qty, nacionalidad'))
            ->groupBy('nacionalidad')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByBorder() //Request $request
    {
        //filters
        $query = DB::table('movimiento_migratorio')
            ->select(DB::raw('count(*) as qty, nombre_frontera'))
            ->groupBy('nombre_frontera')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByReason() //Request $request
    {
        //filters
        $query = DB::table('movimiento_migratorio')
            ->select(DB::raw('count(*) as qty, motivo_viaje'))
            ->groupBy('motivo_viaje')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }    

    public function showLastSyncByBorder($name) //Request $request
    {
        //filters
        $query = DB::table('sincronizacion_frontera')
            ->select(DB::raw('telefono_movil, max(fecha_ultimo_movimiento) ultima_vez, datediff(now(), max(fecha_ultimo_movimiento)) dias,
                CASE
                WHEN datediff(now(), max(fecha_ultimo_movimiento)) <= 1 then "verde"
                WHEN datediff(now(), max(fecha_ultimo_movimiento)) between 2 and 5 then "naranja"
                WHEN datediff(now(), max(fecha_ultimo_movimiento)) > 5 then "rojo"
                else 1 end as alerta'))
            ->where('nombre_frontera', $name)
            ->groupBy('telefono_movil')
            ->orderBy('dias', 'desc')
            ->get();
            //->toSql();

        //var_dump($query); exit();
        //result
        return response()->json($query, 200);
    }    

}