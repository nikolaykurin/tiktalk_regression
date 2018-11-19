<?php namespace Progforce\User\Classes;

use Backend\Facades\BackendAuth;
use Closure;
use Illuminate\Http\Request;
use Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Exception;

class BackendAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = BackendAuth::check();

            if (!$user) {
                throw new UnauthorizedHttpException('Backend', 'User not found');
            }

            return $next($request);
        }
        catch (Exception $e) {
            return response($e->getMessage(), 403);
        }
    }

}
