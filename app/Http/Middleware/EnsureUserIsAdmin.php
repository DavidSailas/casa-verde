<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            abort(403, 'You do not have access to the admin area.');
        }

        // A new account must choose its own password before doing anything else.
        if ($user->must_change_password && ! $request->routeIs('admin.account.*', 'admin.logout')) {
            return redirect()->route('admin.account.edit')
                ->with('status', 'Please choose your own password to continue.');
        }

        return $next($request);
    }
}
