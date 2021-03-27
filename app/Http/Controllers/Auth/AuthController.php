<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //
    public function signup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|max:255',
            ]);
            
            if($validator->fails()) {
                return response([
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $password = Hash::make($request->password);
            $remember_token = Str::random(env('TOKEN_LENGTH'));

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password,
                'remember_token' => $remember_token,
            ]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Registration Successful',
            ]);


        } catch (Exception $errors) {

            return response()->json([
                'status_code' => 500,
                'message' => 'Error Occured in Registration',
                'error' => $errors,
            ]);
        }
    }

    public function login(Request $request)
    {
        try {
            $login = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);
    
            if(!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                return response(['message' => 'Invalid Login Credentials'], 401);
            }
        } catch( Exception $errors) {

            return response()->json([
                'status_code' => 500,
                'message' => 'Error Occured in Login',
                'error' => $errors,
            ]);
        }


        /** @var \App\User|null $user */
        $user = Auth::user();

        // Creating a token without scopes...
        $token_result = $user->createToken('authToken')->accessToken;



        return response()->json([
            'user' => Auth::user(),
            'token_type' => 'Bearer',
            'access_token' => $token_result,
        ]);
    }

    public function logout(Request $request) 
    {
        try {
            //code...
            $request->user()->token()->revoke();
            
            return response()->json([
                'status_code' => 200,
                'message' => 'Successfully Logged out',
            ]);

            $request->session()->regenerateToken();

        } catch (Exception $th) {
            //throw $th;
            return response()->json([
                'status_code' => 500,
                'message' => 'Error Occured in Revoking User Token',
                'error' => $th,
            ]);
        }

    }

    public function getUser(Request $request)
    {
        return $request->user();
    }
}