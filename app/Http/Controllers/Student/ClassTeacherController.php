<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Support\TeacherCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassTeacherController extends Controller
{
    public function index()
    {
        $schoolClasses = SchoolClass::with('classTeachers')
            ->orderBy('name')
            ->get();

        $teachers = $this->teacherStaffQuery()->get();

        $assignedCount = $schoolClasses->filter(fn (SchoolClass $class) => $class->classTeachers->isNotEmpty())->count();

        return view('student.class-teacher-assignment', [
            'schoolClasses' => $schoolClasses,
            'teachers' => $teachers,
            'stats' => [
                'total' => $schoolClasses->count(),
                'assigned' => $assignedCount,
                'unassigned' => $schoolClasses->count() - $assignedCount,
                'teachers' => $teachers->count(),
            ],
        ]);
    }

    public function show($id)
    {
        $class = SchoolClass::with('classTeachers')->findOrFail($id);

        return response()->json([
            'id' => $class->id,
            'name' => $class->name,
            'status' => $class->status,
            'teachers' => $class->classTeachers->map(fn (Staff $teacher) => [
                'id' => $teacher->id,
                'name' => $teacher->full_name,
                'position' => $teacher->position,
                'employee_id' => $teacher->employee_id,
                'picture' => $teacher->picture ? asset($teacher->picture) : null,
            ])->values(),
        ]);
    }

    public function assign(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        $teacher = $this->teacherStaffQuery()
            ->where('staff.id', $request->staff_id)
            ->firstOrFail();

        $alreadyAssignedHere = ClassTeacherAssignment::query()
            ->where('school_class_id', $class->id)
            ->where('staff_id', $teacher->id)
            ->exists();

        if ($alreadyAssignedHere) {
            return back()->with('message_error', 'This teacher is already assigned to this class.');
        }

        DB::transaction(function () use ($class, $teacher) {
            ClassTeacherAssignment::query()
                ->where('staff_id', $teacher->id)
                ->where('school_class_id', '!=', $class->id)
                ->delete();

            ClassTeacherAssignment::create([
                'school_class_id' => $class->id,
                'staff_id' => $teacher->id,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
        });

        return back()->with('message_success', 'Class teacher assigned successfully.');
    }

    public function unassign(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'staff_id' => 'required|exists:staff,id',
        ]);

        $deleted = ClassTeacherAssignment::query()
            ->where('school_class_id', $request->class_id)
            ->where('staff_id', $request->staff_id)
            ->delete();

        if (! $deleted) {
            return back()->with('message_error', 'That teacher is not assigned to this class.');
        }

        return back()->with('message_success', 'Class teacher removed successfully.');
    }

    private function teacherStaffQuery()
    {
        return Staff::query()
            ->where('staff.status', 'Active')
            ->whereHas('user', function ($query) {
                $query->where('user_cat', TeacherCategory::id())
                    ->where('status', 'Active');
            })
            ->orderBy('staff.surname')
            ->orderBy('staff.firstname');
    }
}
