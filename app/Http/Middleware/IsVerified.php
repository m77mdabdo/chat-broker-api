<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->is_verified == 0){
            return response()->json([
                'message'=>'Your are not verified, Please verify your phone number!',
            ],401);
        }

        if(auth()->user()->address == null || auth()->user()->city_id == null){
            return response()->json([
                'message'=>'Please complete your details!'
            ],401);
        }

        return $next($request);
    }
}
