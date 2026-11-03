<?php

namespace App\Http\Middleware;

use App\Services\TeacherAccessService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTeacherOwnsClass
{
    public function __construct(private TeacherAccessService $teacherAccess) {}

    public function handle(Request $request, Closure $next): Response
    {
        $staffId = $this->teacherAccess->staffId();

        if (! $staffId) {
            abort(403);
        }

        $classId = $this->resolveId(
            $request->route('class') ?? $request->route('schoolClass') ?? $request->input('school_class_id')
        );
        $courseId = $this->resolveId($request->route('course'));

        if (! $classId) {
            abort(404);
        }

        $this->teacherAccess->assertCanAccessClass($staffId, $classId, $courseId, $request);

        return $next($request);
    }

    private function resolveId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Model) {
            return (int) $value->getKey();
        }

        return (int) $value;
    }
}
