<?php

namespace App\Http\Middleware;

use App\Services\UserAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMenuAccess
{
    public function __construct(private UserAccessService $access) {}

    public function handle(Request $request, Closure $next): Response
    {
        $routeName = $request->route()?->getName();

        if ($routeName === null && $request->is('teacher-leave')) {
            return $next($request);
        }

        if ($this->access->allowsRoute($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            abort(403, 'You do not have access to that page.');
        }

        return redirect()
            ->route('dashboard')
            ->with('message_error', 'You do not have access to that page.');
    }
}
