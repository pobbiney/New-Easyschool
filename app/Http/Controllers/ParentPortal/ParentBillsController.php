<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\Billing\BillPaymentProcessor;
use App\Services\Billing\PaystackService;
use App\Services\Billing\StudentBillCreditService;
use App\Services\ParentPortal\ParentStudentService;
use App\Support\AcademicPeriodDefaults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentBillsController extends Controller
{
    public function __construct(
        private ParentStudentService $parentStudentService,
        private BillPaymentProcessor $paymentProcessor,
        private StudentBillCreditService $creditService,
        private PaystackService $paystackService,
    ) {}

    public function index(Request $request, Student $student)
    {
        $parent = Auth::guard('parent')->user();
        $child = $this->parentStudentService->findOwnedStudent($parent, $student->id);

        if (! $child) {
            abort(403);
        }

        $yearId = AcademicPeriodDefaults::yearId($request);
        $termId = AcademicPeriodDefaults::termId($request);

        $billsQuery = StudentBill::query()
            ->with(['billingItem', 'setup.academicTerm', 'setup.academicYear'])
            ->where('student_id', $child->id)
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('academic_term_id', $termId))
            ->orderByDesc('id');

        $allBills = $billsQuery->get();
        $outstandingBills = $this->paymentProcessor->outstandingBillsForStudent($child);
        $totalOutstanding = $outstandingBills->sum('balance');
        $creditBalance = $this->creditService->creditBalance($child);

        return view('parent.bills', [
            'parent' => $parent,
            'student' => $child,
            'children' => $this->parentStudentService->childrenFor($parent),
            'bills' => $allBills,
            'outstandingBills' => $outstandingBills,
            'totalOutstanding' => $totalOutstanding,
            'creditBalance' => $creditBalance,
            'netPayable' => max(round($totalOutstanding - $creditBalance, 2), 0),
            'period' => AcademicPeriodDefaults::forFrontend($request),
            'academicYears' => AcademicYear::query()->where('status', 'Active')->orderByDesc('name')->get(),
            'academicTerms' => AcademicTerm::query()->where('status', 'Active')->orderBy('sort_order')->get(),
            'paystackPublicKey' => $this->paystackService->publicKey(),
            'paystackConfigured' => $this->paystackService->isConfigured(),
            'school' => SchoolSetting::current(),
        ]);
    }
}
