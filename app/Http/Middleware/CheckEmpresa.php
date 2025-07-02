<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEmpresa
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next)
{
    $user = $request->user();

    // Si es superadmin, omite la validación por empresa
    if ($user && $user->rol === 'superadmin') {
        return $next($request);
    }

    // Validación para usuarios normales
    $empresaIdToken = $user?->empresa_id;
    $empresaIdRuta = $request->route('empresa_id') ?? $request->input('empresa_id');

    if ($empresaIdRuta && $empresaIdRuta != $empresaIdToken) {
        return response()->json(['message' => 'No autorizado para esta empresa'], 403);
    }

    return $next($request);
}


}
