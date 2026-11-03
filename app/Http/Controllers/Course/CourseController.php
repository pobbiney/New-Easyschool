<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function getAddCourseView()
    {
        $topLevelCourses = Course::topLevel()
            ->with('subCourses')
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => $topLevelCourses->count(),
            'subcourses' => Course::whereNotNull('parent_id')->count(),
            'active' => $topLevelCourses->where('status', 'Active')->count(),
        ];

        return view('course-setup.add-course', [
            'topLevelCourses' => $topLevelCourses,
            'parentCourses' => Course::topLevel()->where('status', 'Active')->orderBy('name')->get(),
            'stats' => $stats,
        ]);
    }

    public function addCourse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
            'course_category' => 'required_without:parent_id',
            'parent_id' => 'nullable|exists:courses,id',
        ]);

        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;
        $parent = $parentId ? Course::topLevel()->where('id', $parentId)->where('status', 'Active')->first() : null;

        if ($parentId && ! $parent) {
            return back()->with('message_error', 'Selected parent course is invalid.');
        }

        if ($this->courseNameExists(trim($request->name), $parentId)) {
            return back()->with('message_error', 'A course with this name already exists at this level.');
        }

        $course = new Course();
        $course->parent_id = $parentId;
        $course->name = trim($request->name);
        $course->description = trim($request->description ?? '');
        $course->category = $parent ? $parent->category : trim($request->course_category);
        $course->status = trim($request->status);
        $course->created_by = Auth::id();
        $course->save();

        return back()->with('message_success', $parentId ? 'Sub-course added successfully.' : 'Course added successfully.');
    }

    public function storeSubCourse(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
            'description' => 'nullable|string',
        ]);

        $parent = Course::topLevel()->where('id', $request->parent_id)->where('status', 'Active')->firstOrFail();

        if ($this->courseNameExists(trim($request->name), $parent->id)) {
            return back()->with('message_error', 'A sub-course with this name already exists under this course.');
        }

        Course::create([
            'parent_id' => $parent->id,
            'name' => trim($request->name),
            'description' => trim($request->description ?? ''),
            'category' => $parent->category,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'Sub-course added successfully.');
    }

    public function getCourseID($id)
    {
        $course = Course::withCount('subCourses')->findOrFail($id);

        return response()->json($course);
    }

    public function updateCourse(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'name' => 'required|string|max:255',
            'status' => 'required',
            'course_category' => 'required_without:parent_id',
            'parent_id' => 'nullable|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);
        $parentId = $request->filled('parent_id') ? (int) $request->parent_id : null;

        if ($parentId === $course->id) {
            return back()->with('message_error', 'A course cannot be its own parent.');
        }

        if ($parentId) {
            $parent = Course::topLevel()->where('id', $parentId)->where('status', 'Active')->first();
            if (! $parent) {
                return back()->with('message_error', 'Selected parent course is invalid.');
            }
        }

        if ($course->subCourses()->exists() && $parentId) {
            return back()->with('message_error', 'Courses with sub-courses must remain top-level.');
        }

        if ($this->courseNameExists(trim($request->name), $parentId, $course->id)) {
            return back()->with('message_error', 'A course with this name already exists at this level.');
        }

        $course->parent_id = $parentId;
        $course->name = trim($request->name);
        $course->description = trim($request->description ?? '');
        $course->category = $parentId && isset($parent)
            ? $parent->category
            : trim($request->course_category);
        $course->status = trim($request->status);
        $course->updated_by = Auth::id();
        $course->save();

        return back()->with('message_success', 'Course updated successfully.');
    }

    private function courseNameExists(string $name, ?int $parentId, ?int $ignoreId = null): bool
    {
        $query = Course::where('name', $name)->where('parent_id', $parentId);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
