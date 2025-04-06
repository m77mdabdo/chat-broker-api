<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Recommendation extends Controller
{
    public function __construct()
    {
        // echo $this->header('Authorization');
        // // Apply auth:sanctum only if user is authenticated
        // $this->middleware(function ($request, $next) {
        //     if ( str_contains($request->header('authorization'), 'Bearer')) {
        //         // dd($request);
        //         $this->middleware('auth:sanctum');
        //     }
        //     return $next($request);
        // });
    }

    public function recommendation(Request $request)
    {

        dd(auth()->user()->id);
        if (auth()->user()->id) {
            // User is authenticated
            // Example: Return personalized recommendations for authenticated users
            $user = $request->user();
            return response()->json(['message' => 'Recommendation for authenticated user']);
        } else {
            // User is not authenticated
            // Example: Return general recommendations for non-authenticated users
            return response()->json(['message' => 'Recommendation for non-authenticated user']);
        }

    }
}
