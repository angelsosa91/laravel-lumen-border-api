<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\Auth;
use  App\Models\Eventos;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EventosController extends Controller
{
     /**
     * Instantiate a new UserController instance.
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
     * Get all User.
     *
     * @return Response
     */
    public function show(Request $request)
    {
        //$users = Usuarios::all();
        //request
        $page = ($request->has('page')) ? intval($request->input('page')) : 1;
        $rows = ($request->has('rows')) ? intval($request->input('rows')) : 50;
        $sort = ($request->has('sort')) ? strval($request->input('sort')) : "id";
        $order = ($request->has('order')) ? strval($request->input('order')) : "asc";
        //$estado = ($request->has('estado')) ? intval($request->input('estado')) : 1;
        $offset = ($page-1)*$rows;
        //filters
        $search = Eventos::where('id', '>', 0);
        //count
        $count = $search->count();
        //This field uses a LIKE match, handle it separately
        if ($request->has('frontera') and !empty($request->input('frontera'))) {            
            $search->where('nombre_frontera', 'like', '%' . $request->input('frontera') . '%');
        } 
        if ($request->has('fecha_desde') and $request->has('fecha_hasta')  and !empty($request->input('fecha_desde')) and !empty($request->input('fecha_hasta'))) {            
            $search->whereBetween(DB::raw('DATE(fecha_ultimo_movimiento)'), [$request->input('fecha_desde'), $request->input('fecha_hasta')]);
        }
        //array
        $result = array(); $items = array();
        //query
        $items = $search->orderBy($sort, $order)->limit($rows)->offset($offset)->get(); //toSql
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

}