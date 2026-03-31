<?php

namespace App\Http\Controllers\UPT;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
         if ($request->hasFile('profile_avatar')) {
            if ($user->profile_avatar) {
                Storage::disk('public')->delete('user/' . $user->profile_avatar);
            }
            $imagePath = $request->file('profile_avatar')->store('user', 'public');
            $validated['profile_avatar'] = basename($imagePath);
        }
        $user->update($validated);
        return response()->json([
        'Updated'=>$user
        ]);
 }    
}
