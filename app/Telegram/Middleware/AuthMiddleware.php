<?php

namespace App\Telegram\Middleware;

use Illuminate\Validation\UnauthorizedException;


class AuthMiddleware
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function handle($request, $next)
    {
        if (!$this->isAuthorized($request)) {
            throw new UnauthorizedException('Unauthorized access.');
        }

        return $next($request);
    }

    protected function isAuthorized($request)
    {
        return $request->headers['Authorization'] === $this->token;
    }
}
