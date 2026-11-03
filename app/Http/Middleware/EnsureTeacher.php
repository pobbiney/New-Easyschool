<?php

namespace App\Http\Middleware;

use App\Services\TeacherAccessService;
use App\Services\UserAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacher
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->guest('/')->with('message_error', 'Please log in to continue.');
        }

        $granted = app(UserAccessService::class)->can('teacher-dashboard');

        if (! $this->teacherAccess->isTeacher() && ! $granted) {
            abort(403, 'This area is only available to teachers with an active login.');
        }

        if (! auth()->user()->staff_id) {
            return redirect('/dashboard')->with('message_error', 'Your account is not linked to a staff profile. Contact an administrator.');
        }

        return $next($request);
    }
}
