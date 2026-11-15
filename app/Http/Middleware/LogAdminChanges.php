<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds a line to the admin history for every change that went through (save, add, delete).
 * It records who, what kind of change and which item, never what was typed.
 */
class LogAdminChanges
{
    /** Requests that only look things up or test something, so they are not "changes". */
    protected const QUIET = [
        'admin.logout',
        'admin.ai-brain.test',
        'admin.ai-brain.teach',
        'admin.ai-brain.connection.check',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodSafe() || $response->getStatusCode() >= 400) {
            return $response;
        }

        $name = (string) $request->route()?->getName();

        if ($name === '' || in_array($name, self::QUIET, true)) {
            return $response;
        }

        // A form sent back with errors saved nothing.
        if ($request->hasSession() && optional($request->session()->get('errors'))->any()) {
            return $response;
        }

        $details = null;

        if ($request->filled('status') && is_string($request->input('status'))) {
            $details = 'Status: '.$request->input('status');
        }

        ActivityLog::record(ActivityLog::describe($name), $this->subject($request), $details);

        return $response;
    }

    /** "Booking CV-ABC123" or "HomeContent Standard Villa", from the item in the address. */
    protected function subject(Request $request): ?string
    {
        foreach ($request->route()?->parameters() ?? [] as $parameter) {
            if ($parameter instanceof Model) {
                $label = $parameter->reference ?? $parameter->title ?? $parameter->name ?? $parameter->reason ?? '#'.$parameter->getKey();

                return trim(class_basename($parameter).' '.$label);
            }
        }

        return null;
    }
}
