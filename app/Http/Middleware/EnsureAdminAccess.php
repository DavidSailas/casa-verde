<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps a role out of the admin areas it does not include: ->middleware('admin.area:bookings').
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next, string $area): Response
    {
        $user = $request->user();

        if (! $user || ! $user->canAccess($area)) {
            abort(403, 'Your role does not include this part of the admin.');
        }

        return $next($request);
    }
}
