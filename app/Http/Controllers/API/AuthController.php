<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use ThrottlesAttempts;

class AuthController extends BaseController
{
    protected $maxAttempts = 5; // Default is 5
    protected $decayMinutes = 2; // Default is 1

    private function encryption($data): string {
        // need to be Encrypted
        $simple_string = $data; //"Welcome to GeeksforGeeks\n";
        // Store the cipher method
        $ciphering = "AES-128-CTR";
        // Use OpenSSl Encryption method
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        // Non-NULL Initialization Vector for encryption
        $encryption_iv = '1234567891011121';
        // Store the encryption key
        $encryption_key = "Test123...";
        // Use openssl_encrypt() function to encrypt the data
        $encryption = openssl_encrypt($simple_string, $ciphering, $encryption_key, $options, $encryption_iv);
        // Display the encrypted string
        return base64_encode($encryption);
    }

    public function login(Request $request)
    {
        /*if ($this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);
			return $this->sendLockoutResponse($request);
		}*/
        $captcha = $request->validate([
            'grecaptcha' => 'required'
        ]);

        $post_data = http_build_query(
            array(
                'secret' => '1234567890',
                'response' => $request->input('grecaptcha'), //$_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if (!$result->success) {
            //throw new Exception('Gah! CAPTCHA verification failed. Please email me directly at: jstark at jonathanstark dot com', 1);
            return response(['message' => 'Verificacion de Captcha fallida'], 500);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $credentials['estado'] = 1;

        if (!auth()->attempt($credentials)) {
            return response(['message' => 'Credenciales incorrectas'], 500);
        }

        //$this->clearAttempts($request);

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return response(['hasValueOne' => $this->encryption('index'), 'hasValueTwo' => $this->encryption($accessToken), 'hasValueThree' => $this->encryption(json_encode(auth()->user())), 'hasValueFour' => $this->encryption('6LccpKIhAAAAAISiRyxLjHIRwkzDyThvTuthoq958L3B0')]);

    }

    public function logout (Request $request) {
        $token = $request->user()->token();
        $token->revoke();
        $response = ['success' => true, 'message' => 'You have been successfully logged out!'];
        return response($response, 200);
    }

    /*
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:55',
            'email' => 'email|required|unique:users',
            'username' => 'required|max:45',
            'password' => 'required|confirmed',
            'rol' => 'required'
            //'estado' => 'required'
        ]);

        $validatedData['password'] = bcrypt($request->password);

        $user = User::create([
            'name' => strtoupper($request->input('name')),
            'username' => str_replace(' ', '', trim(strtolower($request->input('username')))),
            'email' => strtolower($request->input('email')),
            'rol' => intval($request->input('rol')),
            'password' => bcrypt($request->password),
        ]);
        //app('hash')->make($request->input('password'))

        $accessToken = $user->createToken('authToken')->accessToken;

        return response([ 'user' => $user, 'access_token' => $accessToken]);
    }
    */

    /*
    public function signin(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $authUser = Auth::user();
            $success['token'] =  $authUser->createToken('BorderInHandsOIM2k21')->plainTextToken;
            $success['name'] =  $authUser->name;
            $success['rol'] =  $authUser->rol;
            $success['id'] =  $authUser->id;

            return $this->sendResponse($success, 'User signed in');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'rol' => 'required',
            'estado' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if($validator->fails()){
            return $this->sendError('Error validation', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyAuthApp')->plainTextToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User created successfully.');
    }
    */
}
