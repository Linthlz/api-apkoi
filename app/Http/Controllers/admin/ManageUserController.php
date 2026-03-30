<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
    public function UserList(Request $request){
        $this->authorize('manage users');
        $user = $request->user();
        $userList = User::all()->filter(function($user){
            return !$user->hasRole('super-admin');
        })->values();
        return response()->json([
            "Users"=>$userList
        ],200);
    }

    public function ModifyUser(Request $request,$public_id ){
        $user = $request->user();
        $this->authorize('manaage users');
        $modifyUser = User::findOrFail($public_id);
        if(isset($validated['password'])){
            $validated['password']= Hash::make($validated['password']);
        }
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'nullable','email', Rule::unique('users','email')->ignore($modifyUser->publlic_id)],
            'password' => ['sometimes','string', 'min:10'],
        ]);
        $modifyUser->update($validated);
        return response()->json([
            'Updated'=> $modifyUser
        ],200);
    }

    public function destroy(Request $request, $public_id){
        $user = $request->user();
        $this->authorize('manage users');
        $destroyUser = User::findOrFail($public_id)->delete();
        return response()->json([
            "User Deleted" => $destroyUser
        ],200);
    }
}
