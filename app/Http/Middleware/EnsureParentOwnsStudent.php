<?php

namespace App\Http\Middleware;

use App\Services\ParentPortal\ParentStudentService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureParentOwnsStudent
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $student = $request->route('student');

        if (! $student) {
            abort(404);
        }

        $parent = auth('parent')->user();

        if (! $parent || ! $this->parentStudentService->ownsStudent($parent, $student)) {
            abort(403, 'You do not have access to this student record.');
        }

        return $next($request);
    }
}
