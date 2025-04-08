<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AssisFiles;
use App\Models\Migrations;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AssisFilesController extends Controller
{
    public function showById($id)
    {
        $search = DB::table('registro_casos_asistencia_archivos')
            ->select('id', 'descripcion', 'path', 'extension')
            ->where('fk_registro_casos_asistencia', '=', $id);
        //count
        $count = $search->count();
        //query
        $mov = $search->get();
        //echo $mov; exit;
        //array
        $result = array(); $items = array();
        //fetch
        foreach ($mov as $m) {
            //push
            array_push($items, $m);
        }
        //result
        $result["total"] = $count;
        $result["rows"] = $items;
        //return
        return response()->json($result, 200);
    }

    //delete
    public function removeFileById($id)
    {
        //try
        try {
            //find
            $assis = AssisFiles::findOrFail($id);
            $assis->delete();
            //return successful response
            return response()->json(['message' => 'DELETED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Assis File Deleted Failed!'], 409);
        }
    }

    public function uploadFile(Request $request, $id) {
        $input = $request->all();

        $validator = Validator::make($input, [
            'descripcion' => 'required'
        ]);

        if($validator->fails()){
            //return $this->sendError($validator->errors());
            return response()->json(['message' => $validator->errors()], 409);
        }
        //Move Uploaded File to public folder
        try {
            $destinationPath = public_path('files');
            if($request->hasFile('archivo')){
                $allowedfileExtension=['pdf','jpg','png','docx'];
                //$file = $request->file('archivo');
                $filename = auth()->id() . '_' . time() . '.'. $request->archivo->extension(); //$file->getClientOriginalName();
                $extension = $request->archivo->extension(); // $file->getClientOriginalExtension();
                $mime = $request->archivo->getClientMimeType();
                $check=in_array($extension,$allowedfileExtension);
                //dd($check);
                if($check){
                    $request->archivo->move($destinationPath, $filename);
                    //find
                    $files = new AssisFiles;
                    $files->fk_registro_casos_asistencia = $id;
                    $files->descripcion = strtoupper($request->input('descripcion'));
                    $files->path = $filename;
                    $files->extension = $mime;
                    $files->save();
                    //return successful response
                    return response()->json(['files' => $files, 'message' => 'CREATED'], 201);
                } else {
                    return response()->json(['message' => 'Created File Failed!'], 409);
                }
            }
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Created File Failed!'], 409);
        }
    }

    public function viewFile($id){
        try{
            $files = AssisFiles::findOrFail($id);
            return response()->json(['file' => base64_encode(File::get(public_path('files/'.$files->path))), 'extension' => $files->extension, 'message' => 'DOWNLOAD'], 200);
            //return File::get(public_path('files/'.$case->path)); // response()->file(public_path('files/'.$case->path));
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'Get File Failed!'], 422);
        }
    }
}
