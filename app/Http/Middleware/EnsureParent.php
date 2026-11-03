<?php

namespace App\Http\Middleware;

use App\Models\ParentPortal\ParentAccount;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParent
{
    public function handle(Request $request, Closure $next): Response
    {
        $parent = auth('parent')->user();

        if (! $parent instanceof ParentAccount || ! $parent->isActive()) {
            auth('parent')->logout();

            return redirect()->route('parent.login')
                ->with('message_error', 'Please sign in to the parent portal.');
        }

        return $next($request);
    }
}
