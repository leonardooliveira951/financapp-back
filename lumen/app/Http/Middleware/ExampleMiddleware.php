<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class ExampleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $authorization = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorization);

        try {
            $user = JWT::decode($token, 'YRr9wFSzYzQGwkFsnzvqQhcmNUjDGBwZ', array('HS256'));
        } catch (ExpiredException $error) {
            return response("Token expirado", 401);
        }

        $request->setUserResolver(function () use ($user) {
            return (array) $user;
        });

        return $next($request);
    }
}
