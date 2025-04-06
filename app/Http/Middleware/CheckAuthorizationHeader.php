<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class CheckAuthorizationHeader
{
    protected  $router;
    public function __construct(Router $router)
    {
        $this->router = $router;
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $middleware)
    {



        if ($request->hasHeader('authorization')) {
            // Apply auth:sanctum middleware if the header exists
            return app()->handle($request, function ($request) use ($next) {
                return $next($request);
            }, 'auth:sanctum');
        }




        return $next($request);
    }
}
