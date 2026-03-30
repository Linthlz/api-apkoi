<?php

namespace App\Http\Controllers\UPT;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
 use AuthorizesRequests;

    //  'name',
    //     'public_id',
    //     'email',
    //     'password',
    //     'role',
    //     'profile_avatar'

 public function modifyProfile(Request $request){
    $user = $request->user();
    $this->authorize('manage profile');
    $validated=$request->validate([
        'name' =>['sometimes', 'string'],
        'email'=> ['sometimes', 'email', 'unique:users,email,' . $user -> id],
        'password'=> ['sometimes', 'confirmed', 'min:10'],
        'profile_avatar' => ['sometimes', 'string'],
    ]);
    if(isset($validated['password'])){
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        return response()->json([
        'Updated'=>$user
        ]);
 }    
}
