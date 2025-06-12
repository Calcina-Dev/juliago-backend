<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificarRol
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (count($roles) === 1 && strpos($roles[0], ',') !== false) {
            $roles = explode(',', $roles[0]);
        }

        #Log::info('Middleware VerificarRol user rol:', ['rol' => $user?->rol, 'rolesPermitidos' => $roles]);

        if (!$user || !in_array($user->rol, $roles)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
