<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        // $user->sendEmailVerificationNotification();
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

    public function modifyUser(Request $request, $public_id){
        $this->authorize('manage users');
        $modifyUser = User::where('public_id', $public_id)->firstOrFail();
        $validated = $request->validate([
            'name' => ['sometimes', 'string'],
            'email' => ['sometimes','nullable','email', Rule::unique('users', 'email')->ignore($modifyUser->public_id, 'public_id')],
            'password' => ['sometimes', 'string', 'min:10'],
            'profile_avatar' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        if ($request->hasFile('profile_avatar')) {
            if ($modifyUser->profile_avatar) {
                Storage::disk('public')->delete('user/' . $modifyUser->profile_avatar);
            }
            $imagePath = $request->file('profile_avatar')->store('user', 'public');
            $validated['profile_avatar'] = basename($imagePath);
        }
        $modifyUser->update($validated);
        return response()->json([
            'updated' => $modifyUser
        ], 200);
    }

    public function destroy(Request $request, $public_id){
        $user = $request->user();
        $this->authorize('manage users');
        $destroyUser = User::where('public_id', $public_id)->firstOrFail();
         try {
            DB::beginTransaction();
            Storage::disk('public')->delete('user/' . $destroyUser->profile_avatar);
            $destroyUser->delete();
            DB::commit();
            return response()->json([
                'message' => 'User Deleted'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
