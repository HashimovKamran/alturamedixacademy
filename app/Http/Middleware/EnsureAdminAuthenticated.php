<?php

namespace App\Http\Middleware;

use App\Services\Admin\AdminAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAuthenticated
{
    public function __construct(private readonly AdminAuthService $auth)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->auth->user($request)) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}