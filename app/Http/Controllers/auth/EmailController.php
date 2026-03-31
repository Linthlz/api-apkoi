<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailController extends Controller
{
    public function Verify(Request $request, $id, $hash):JsonResponse
    {
        $user = User::find($id);
        if(!hash_equals(sha1($user->getEmailForVerification()), $hash)){
            abort(403, 'Invalid Link');
        }
        if($user->hasVerifiedEmail()){
            return response()->json([
                'message'=>'email already verified'
            ]);
            $user->markEmailAsVerified();
            event(new Verified($user));
            return response()->json([
                '{+}'=> 'Email Verified'
            ]);
        }
        $user->markEmailAsVerified();
        event(new Verified($user));

        return response()->json([
            '{+}'=> 'Email Verified'
        ]);
    }
}