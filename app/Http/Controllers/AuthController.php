<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request){
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password'  => ['required']
        ]);
        $user = $request->user();
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'Login' => 'Success',
            'Token' => $token,
            'Data'=>[
                'name' => $user->name,
                'role' => $user->getRoleNames(),  
            ]
        ],200);
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            '{+}'=>'User Logged Out!'
        ],200);
    }
}
