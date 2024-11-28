<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller
{
    public function signup(Request $request){
        $validateUser = Validator::make(
            $request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validateUser->error->all()
            ],401);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Created successfully.',
            'user' => $user
        ],200);
    }

    public function login(Request $request){
        $validateUser = Validator::make(
            $request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'User Authentication Fails',
                'errors' => $validateUser->error->all()
            ],404);
        }

        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $authUser = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully.',
                'token' =>  $authUser -> createToken("API TOKEN")->plainTextToken,
                'token_type' => 'bearer'
            ],200);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'User Email and password not matched.'
            ],401);
        }
    }

    public function logout(Request $request){
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'You logged out successfully.'
        ],200);
    }
}
