<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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
        $schoolClasses = SchoolClass::with('classTeacher')
            ->orderBy('name')
            ->get();

        $teachers = $this->teacherStaffQuery()->get();

        $assignedCount = $schoolClasses->whereNotNull('class_teacher_id')->count();

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
        $class = SchoolClass::with('classTeacher')->findOrFail($id);

        return response()->json([
            'id' => $class->id,
            'name' => $class->name,
            'status' => $class->status,
            'class_teacher_id' => $class->class_teacher_id,
            'teacher' => $class->classTeacher ? [
                'id' => $class->classTeacher->id,
                'name' => $class->classTeacher->full_name,
                'position' => $class->classTeacher->position,
                'employee_id' => $class->classTeacher->employee_id,
                'picture' => $class->classTeacher->picture,
            ] : null,
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

        DB::transaction(function () use ($class, $teacher) {
            SchoolClass::where('class_teacher_id', $teacher->id)
                ->where('id', '!=', $class->id)
                ->update([
                    'class_teacher_id' => null,
                    'updated_by' => Auth::id(),
                ]);

            $class->class_teacher_id = $teacher->id;
            $class->updated_by = Auth::id();
            $class->save();
        });

        return back()->with('message_success', 'Class teacher assigned successfully.');
    }

    public function unassign(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        $class->class_teacher_id = null;
        $class->updated_by = Auth::id();
        $class->save();

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
