<?php

namespace App\Http\Controllers\TeacherManagement;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Support\TeacherCategory;

class TeacherDirectoryController extends Controller
{
    public function index()
    {
        $teachers = Staff::query()
            ->with(['user', 'assignedClass', 'courseTeachingAssignments.schoolClass', 'courseTeachingAssignments.course'])
            ->where('staff.status', 'Active')
            ->whereHas('user', function ($query) {
                $query->where('user_cat', TeacherCategory::id())
                    ->where('status', 'Active');
            })
            ->orderBy('staff.surname')
            ->orderBy('staff.firstname')
            ->get();

        $withLogin = $teachers->filter(fn ($t) => $t->user)->count();
        $homeroomAssigned = $teachers->filter(fn ($t) => $t->assignedClass)->count();
        $subjectAssigned = $teachers->filter(fn ($t) => $t->courseTeachingAssignments->isNotEmpty())->count();

        return view('teacher-management.teacher-directory', [
            'teachers' => $teachers,
            'stats' => [
                'total' => $teachers->count(),
                'with_login' => $withLogin,
                'homeroom' => $homeroomAssigned,
                'subject' => $subjectAssigned,
            ],
        ]);
    }
}
