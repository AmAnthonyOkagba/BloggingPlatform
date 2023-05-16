<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * User Login
     */
    public function login(Request $request)
    {
        // Validations
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $token =  $user->createToken('blogApp')->accessToken;
            // return response()->json(['success' => $success], $this->successStatus);
            return response()->json([
                'stauts' => 'Success',
                'User' => $user,
                'token' =>  $token,
            ]);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Incorrect email or password',
            ], 401);
        }
    }

    /**
     * Register
     */
    public function register(Request $request)
    {

		$validator = Validator::make($request->all(),[
            'email' => 'required|unique:users,email|email|max:255',
            'username'  =>  'required|unique:users,username|alpha_dash|min:5',
            'password'  =>  'required|min:6',
            'password_confirmation'	=>	'required|same:password',
        ]);

        if($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validator->errors()
            ], 401);
        }

        $user = User::create([
            'username'  => $request->username,
            'email' =>  $request->email,
            'password'  => Hash::make($request->password),
        ], 201);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {

            $user = Auth::user();
            $token =  $user->createToken('blogApp')->accessToken;

            return response()->json([
                'status' => true,
                'message' => 'User Registered Successfully',
                'user' => $user,
                'token' =>  $token,
            ], 200);
        }
        else
        {
            return response()->json([
                'status'=>false,
                'message'=>'Registration failed, please try again.'
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $user = Auth::user();

        if ($user) {
            // Revoke the user's access token
            $user->token()->revoke();
            
            $user->token()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Successfully logged out',
            ], 200);
        }
        return response()->json([
            'status' => false,
            'errors' => 'Something happened',
        ], 401);
    }
}
