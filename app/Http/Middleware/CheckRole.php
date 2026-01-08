<?php
// app/Http/Middleware/CheckRole.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param string $role Le rôle requis ou plusieurs rôles séparés par des pipes (|)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Vérifier si l'utilisateur a un des rôles requis
        $hasRole = false;
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            // Pour les requêtes API
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                    'error' => 'Forbidden',
                    'required_roles' => $roles,
                    'user_role' => $request->user()->user_role
                ], 403);
            }

            // Pour les requêtes web, lancer une exception
            throw new AccessDeniedHttpException(
                "Vous n'avez pas les permissions nécessaires pour accéder à cette ressource. Rôle requis : " . implode(' ou ', $roles)
            );
        }

        return $next($request);
    }
}
