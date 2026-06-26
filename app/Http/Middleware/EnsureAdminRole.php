<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function __construct(private readonly AdminAuthService $auth) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = $this->auth->user($request);
        abort_unless($admin && $admin->hasAnyRole($roles), 403, 'Bu əməliyyat üçün icazəniz yoxdur.');

        return $next($request);
    }
}
