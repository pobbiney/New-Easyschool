<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Models\StudentBill;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function index()
    {
        $parent = Auth::guard('parent')->user();
        $children = $this->parentStudentService->childrenFor($parent);

        $summaries = $children->map(function ($student) {
            $outstanding = (float) StudentBill::query()
                ->where('student_id', $student->id)
                ->where('balance', '>', 0)
                ->sum('balance');

            return [
                'student' => $student,
                'class_name' => $student->schoolClass?->name ?? $student->class_name,
                'outstanding' => $outstanding,
            ];
        });

        return view('parent.dashboard', [
            'parent' => $parent,
            'children' => $children,
            'summaries' => $summaries,
            'school' => SchoolSetting::current(),
        ]);
    }
}
