<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ManageUserController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request){
        $this->authorize('manage users');

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:10'],
        ]);
        $uuid = Str::uuid()->toString();
        $role = 'univ-upt';
        $user = User::create([
            'name'=> $validated['name'],
            'email'=> $validated['email'],
            'password'=> Hash::make($validated['password']),
            'public_id'=> $uuid,
        ]);
        $user->assignRole($role);
        return response()->json([
            '{+}' => ucfirst($role). 'Created',
            'user'=>$user
        ],201);
    }
}
