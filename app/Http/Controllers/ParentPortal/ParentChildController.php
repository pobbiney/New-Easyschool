<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Support\Facades\Auth;

class ParentChildController extends Controller
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function show(Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $child->load(['schoolClass.category']);

        $outstanding = (float) StudentBill::query()
            ->where('student_id', $child->id)
            ->where('balance', '>', 0)
            ->sum('balance');

        $children = $this->parentStudentService->childrenFor($parent);

        return view('parent.child', [
            'parent' => $parent,
            'student' => $child,
            'children' => $children,
            'outstanding' => $outstanding,
            'school' => SchoolSetting::current(),
        ]);
    }
}
