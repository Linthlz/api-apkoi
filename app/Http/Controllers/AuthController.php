<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{

public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'message' => 'Login Success',
            'token' => $token,
            'data' => [
                'public_id' => $user->public_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames(),
                // dipakai frontend untuk menampilkan menu dashboard sesuai hak akses
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]
        ], 200);
    }

    public function logout(Request $request){
        // hanya cabut token yang sedang dipakai, sesi di perangkat lain tetap aktif
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            '{+}'=>'User Logged Out!'
        ],200);
    }
}
