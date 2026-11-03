<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\BillPayment;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Support\Facades\Auth;

class ParentPaymentsController extends Controller
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function index(Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $payments = BillPayment::query()
            ->with(['allocations.studentBill.billingItem'])
            ->where('student_id', $child->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get();

        return view('parent.payments', [
            'parent' => $parent,
            'student' => $child,
            'children' => $this->parentStudentService->childrenFor($parent),
            'payments' => $payments,
            'school' => SchoolSetting::current(),
        ]);
    }

    public function receipt(Student $student, BillPayment $payment)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child || $payment->student_id !== $child->id) {
            abort(403);
        }

        $payment->load([
            'student.schoolClass.category',
            'allocations.studentBill.billingItem',
        ]);

        return view('billing.payment-receipt', [
            'payment' => $payment,
            'school' => SchoolSetting::current(),
            'statementUrl' => route('parent.bills', $child),
            'backUrl' => route('parent.dashboard'),
            'backLabel' => 'Back to Dashboard',
        ]);
    }
}
