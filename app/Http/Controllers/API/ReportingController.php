<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ReportingController extends Controller
{
    public function showByDocument() //Request $request
    {
        //filters
        $query = DB::table('registro_casos')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_casos.fk_documentos')
            ->select(DB::raw('count(*) as qty, tipo_documentos'))
            ->groupBy('tipo_documentos')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByCountry() //Request $request
    {
        //filters
        $query = DB::table('registro_casos')
            ->join('documentos', 'documentos.id_documento', '=', 'registro_casos.fk_documentos')
            ->join('data_paises', 'data_paises.id', '=', 'documentos.fk_data_nacionalidad')
            ->select(DB::raw('count(*) as qty, data_paises.descripcion as nacionalidad'))
            ->groupBy('data_paises.descripcion')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByBorder() //Request $request
    {
        //filters
        $query = DB::table('info_viaje')
            ->join('data_fronteras', 'data_fronteras.id', '=', 'info_viaje.fk_data_frontera')
            ->select(DB::raw('count(*) as qty, descripcion as nombre_frontera'))
            ->groupBy('descripcion')
            ->orderBy('qty', 'desc')
            ->get();
        //result
        return response()->json($query, 200);
    }

    public function showByDestination() //Request $request
    {
        //filters
        $query = DB::table('info_viaje')
            ->join('data_destino_final', 'data_destino_final.id', '=', 'info_viaje.fk_data_destino_final')
            ->select(DB::raw('count(*) as qty, descripcion as nombre_frontera'))
            ->groupBy('descripcion')
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
