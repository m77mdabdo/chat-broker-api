<?php

namespace App\Http\Middleware;
use App\Models\Product;
use Closure;
use Illuminate\Http\Request;

class CheckUserRole
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
    $user = $request->user();
/*
    if ($user && $user->role === 'admin') {
       
        return $next($request);
     } elseif ($user && $user->role === 'subscriber') {
        
        return $next($request);
    } else {
        
        return response()->json(['message' => 'Unauthorized'], 403);
    }
*/ 
    // if ($user && ($user->role === 'superadmin' ||$user->role === 'admin'|| $user->role === 'subscriber')) {
        // return $next($request);
    // } else {
        // return response()->json(['message' => 'Unauthorized'], 403);
    // }

    if ($user && ($user->role === 'superadmin' || $user->role === 'admin' || $user->role === 'subscriber')) {
        return $next($request);
    } elseif ($user && $user->role === 'basic') {
    
        $productCount = Product::where('user_id', $user->id)->count();
        
        if ($productCount < 5000) {
            return $next($request);
        } else {
            return response()->json(['message' => 'You are allowed to upload only 5 products.'], 403);
        }
    } else {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
     } 
