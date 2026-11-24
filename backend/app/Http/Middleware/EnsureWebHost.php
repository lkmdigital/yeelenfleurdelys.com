<?php

namespace App\Http\Middleware;

use App\Support\Nav;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Espace gérant (Blade) : réservé aux comptes « host » (propriétaire ou collaborateur).
 * Un collaborateur n'ouvre que les modules cochés par le propriétaire (mêmes clés que le menu).
 */
class EnsureWebHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !$user->isHost()) {
            return redirect(Nav::home($user))->with('error', "Cet espace est réservé aux gérants d'établissement.");
        }

        if ($user->isStaff()) {
            $route = (string) $request->route()?->getName();
            foreach (Nav::hostItems() as [, $name, , $perm]) {
                if ($perm && ($route === $name || str_starts_with($route, $name . '.'))
                    && !in_array($perm, $user->staffPermissions(), true)) {
                    return redirect()->route('host.dashboard')
                        ->with('error', "Vous n'avez pas accès à ce module. Demandez l'accès au propriétaire de l'établissement.");
                }
            }
        }

        return $next($request);
    }
}
