<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentAccount;
use App\Models\Student;
use App\Services\ParentPortal\ParentAccountService;
use App\Services\ParentPortal\ParentStudentService;
use App\Support\GhanaPhone;
use Illuminate\Http\Request;

class AdminParentAccountController extends Controller
{
    public function __construct(
        private ParentAccountService $parentAccounts,
        private ParentStudentService $parentStudents,
    ) {}

    public function index()
    {
        $accounts = ParentAccount::query()
            ->orderByDesc('id')
            ->get()
            ->map(function (ParentAccount $account) {
                $children = $this->parentStudents->childrenFor($account);

                return [
                    'id' => $account->id,
                    'phone' => $account->phone,
                    'guardian_name' => $account->guardian_name ?: '—',
                    'status' => $account->status,
                    'must_change_password' => (bool) $account->must_change_password,
                    'last_login_at' => $account->last_login_at?->format('d M Y, h:i A'),
                    'created_at' => $account->created_at?->format('d M Y'),
                    'children' => $children->map(fn (Student $child) => [
                        'id' => $child->id,
                        'student_id' => $child->student_id,
                        'full_name' => $child->full_name,
                        'class_name' => $child->class_name ?: 'Unassigned',
                    ])->values()->all(),
                    'children_count' => $children->count(),
                ];
            });

        return view('user-management.parent-accounts', [
            'accounts' => $accounts,
            'stats' => [
                'total' => $accounts->count(),
                'active' => $accounts->where('status', ParentAccount::STATUS_ACTIVE)->count(),
                'with_children' => $accounts->where('children_count', '>', 0)->count(),
            ],
            'defaultPassword' => (string) config('parent.default_password', 'Parent123'),
        ]);
    }

    public function searchStudents(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:100',
        ]);

        $query = trim($validated['q'] ?? '');

        if (strlen($query) < 2) {
            return response()->json(['students' => []]);
        }

        $students = Student::query()
            ->where('status', 'Active')
            ->where(function ($q) use ($query) {
                $q->where('student_id', 'like', "%{$query}%")
                    ->orWhere('firstname', 'like', "%{$query}%")
                    ->orWhere('othername', 'like', "%{$query}%")
                    ->orWhere('surname', 'like', "%{$query}%")
                    ->orWhere('class_name', 'like', "%{$query}%")
                    ->orWhere('guardian_phone', 'like', "%{$query}%")
                    ->orWhere('guardian_name', 'like', "%{$query}%");
            })
            ->orderBy('surname')
            ->orderBy('firstname')
            ->limit(30)
            ->get()
            ->map(function (Student $student) {
                $normalized = GhanaPhone::normalize($student->guardian_phone);
                $existing = $normalized
                    ? ParentAccount::query()->where('phone', $normalized)->first()
                    : null;

                return [
                    'id' => $student->id,
                    'student_id' => $student->student_id,
                    'full_name' => $student->full_name,
                    'class_name' => $student->class_name ?: 'Unassigned',
                    'picture' => $student->picture ? asset($student->picture) : null,
                    'initials' => strtoupper(substr((string) $student->firstname, 0, 1).substr((string) $student->surname, 0, 1)),
                    'guardian_name' => $student->guardian_name,
                    'guardian_phone' => $student->guardian_phone,
                    'father_name' => $student->father_name,
                    'father_phone' => $student->father_phone,
                    'mother_name' => $student->mother_name,
                    'mother_phone' => $student->mother_phone,
                    'has_parent_account' => (bool) $existing,
                    'parent_account_phone' => $existing?->phone,
                ];
            });

        return response()->json(['students' => $students]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'guardian_name' => 'nullable|string|max:120',
            'guardian_phone' => 'required|string|max:30',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        if ($student->status !== 'Active') {
            return back()->with('message_error', 'Only Active students can be used to create a parent account.');
        }

        $normalized = GhanaPhone::normalize($validated['guardian_phone']);
        if (! $normalized) {
            return back()
                ->withInput()
                ->with('message_error', 'Enter a valid Ghana phone number for the guardian.');
        }

        $wasExisting = ParentAccount::query()->where('phone', $normalized)->exists();

        $student->guardian_name = trim((string) ($validated['guardian_name'] ?? '')) ?: $student->guardian_name;
        $student->guardian_phone = trim($validated['guardian_phone']);
        $student->save();

        $account = $this->parentAccounts->syncFromStudent($student->fresh());

        if (! $account) {
            return back()
                ->withInput()
                ->with('message_error', 'Could not create the parent account. Check the guardian phone and try again.');
        }

        $defaultPassword = (string) config('parent.default_password', 'Parent123');
        $action = $wasExisting ? 'updated' : 'created';

        return back()->with(
            'message_success',
            'Parent account '.$action.' for '.$account->phone.'. Login uses the guardian phone'
            .($wasExisting ? '.' : ' and default password '.$defaultPassword.'.')
        );
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:30',
        ]);

        $account = $this->parentAccounts->resetToDefault($validated['phone']);

        if (! $account) {
            return back()->with('message_error', 'Parent account not found or inactive.');
        }

        $defaultPassword = (string) config('parent.default_password', 'Parent123');

        return back()->with(
            'message_success',
            'Password reset for '.$account->phone.'. Temporary password: '.$defaultPassword.'. The parent must change it on next login.'
        );
    }
}
