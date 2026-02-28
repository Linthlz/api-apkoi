<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class userrole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        // // $user = auth()->user();
        // if(!Auth::check()){
        //     abort(401, 'Unauthenticated');
        // }
        // $allowed = collect($role)
        // ->map(fn($role) => userrole::from($role) )
        // ->contain($user->role);
        
    if(Auth::user()->role !== 'user'){
        // Auth::logout();
        abort(403, 'Unauthorized Access!');
        // return redirect()
        // ->to(route('login'));
    }
        return $next($request);
    }
}
