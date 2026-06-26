<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureVisualBuilderPayloadSize
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if((int) $request->header('Content-Length', 0) > 1_048_576, 413, 'Page Builder payload maksimum 1 MB ola bilər.');
        return $next($request);
    }
}
