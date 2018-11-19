<?php namespace Progforce\User\Classes;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Exception;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                throw new UnauthorizedHttpException('jwt-auth', 'User not found');
            }

            $request->attributes->add(['user' => $user]);

            return $next($request);
        }
        catch (Exception $e) {
            return response($e->getMessage(), 403);
        }
    }
}
