<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoPasswordResetIsEnabled
{
    public static function enabled(): bool
    {
        return app()->environment('local', 'testing') && config('auth.demo_password_reset');
    }

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(self::enabled(), 404);

        return $next($request);
    }
}
