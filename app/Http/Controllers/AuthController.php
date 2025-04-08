<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuarios;

class AuthController extends Controller
{
    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'nombre' => 'required|string',
            'usuario' => 'required|string|unique:usuarios',
            'password' => 'required|confirmed',
            'rol' => 'required',
        ]);
        //trye
        try {
            //model
            $user = new Usuarios;
            $user->nombre = strtoupper($request->input('nombre'));
            $user->usuario = strtolower($request->input('usuario'));
            $user->rol = intval($request->input('rol'));
            $user->password = app('hash')->make($request->input('password'));
            $user->uuid = Str::uuid()->toString();
            //save
            $user->save();
            //return successful response
            return response()->json(['user' => $user, 'message' => 'CREATED'], 201);
            //end
        } catch (\Exception $e) {
            //return error message
            return response()->json(['message' => 'User Registration Failed!'], 409);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'usuario' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['usuario', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
}