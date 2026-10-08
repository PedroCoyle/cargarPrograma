<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        $tieneAlguno = $user && collect($roles)->contains(
            fn ($rol) => $user->tieneRol((int) $rol)
        );

        if (! $tieneAlguno) {
            abort(403, 'No tenés permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}