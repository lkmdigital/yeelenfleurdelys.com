<?php

namespace App\Http\Middleware;

use App\Support\Nav;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Administration globale (Blade) : réservée aux administrateurs. */
class EnsureWebAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user || !($user->isAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin'))) {
            return redirect(Nav::home($user))->with('error', 'Cet espace est réservé aux administrateurs.');
        }

        return $next($request);
    }
}
