<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $response = $next($request);
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');

            return $response;
        }

        if ($this->isPrefetch($request)) {
            return response('', 204);
        }

        $request->session()->put('url.intended', $request->getRequestUri());

        return redirect('/');
    }

    private function isPrefetch(Request $request): bool
    {
        $purpose = strtolower((string) $request->headers->get('Sec-Purpose', $request->headers->get('Purpose', '')));

        return str_contains($purpose, 'prefetch')
            || $request->headers->get('X-Moz') === 'prefetch';
    }
}
