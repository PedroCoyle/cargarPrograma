<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $rolId = $request->user()?->rol_id;

        if (! $rolId || ! in_array($rolId, $roles)) {
            abort(403, 'No tenés permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}