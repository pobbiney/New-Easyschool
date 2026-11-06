<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authentication\AuthenticationController;
use App\Http\Controllers\Course\CourseController;
use App\Http\Controllers\Course\CourseTeacherController;
use App\Http\Controllers\Course\CourseRegistrationController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\UserManagement\UserManagementController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\DepartmentController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\LeaveController;
use App\Http\Controllers\Hr\AttendanceController as HrAttendanceController;
use App\Http\Controllers\Hr\SalaryStructureController;
use App\Http\Controllers\Hr\PayrollController;
use App\Http\Controllers\Hr\SettingController as HrSettingController;
use App\Http\Controllers\Hr\AppraisalController;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\AcademicYearController;
use App\Http\Controllers\Student\AcademicTermController;
use App\Http\Controllers\Student\SchoolClassController;
use App\Http\Controllers\Student\ClassCategoryController;
use App\Http\Controllers\Student\ClassTeacherController;
use App\Http\Controllers\Student\StudentClassAssignmentController;
use App\Http\Controllers\Student\StudentPromotionController;
use App\Http\Controllers\Student\StudentRemarkController;
use App\Http\Controllers\Settings\SchoolSettingController;
use App\Http\Controllers\IdCard\CardSettingController;
use App\Http\Controllers\IdCard\PrintIdCardController;
use App\Http\Controllers\Settings\AssessmentTypeController;
use App\Http\Controllers\Settings\PromotionSettingController;
use App\Http\Controllers\Dormitory\DormitoryController;
use App\Http\Controllers\Billing\BillingItemController;
use App\Http\Controllers\Billing\CategoryBillSetupController;
use App\Http\Controllers\Billing\StudentBillController;
use App\Http\Controllers\Billing\BillPaymentController;
use App\Http\Controllers\TeacherManagement\TeacherDirectoryController;
use App\Http\Controllers\TeacherManagement\GradingSchemeController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ClassWorkspaceController;
use App\Http\Controllers\Teacher\CourseWorkspaceController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\GradebookController;
use App\Http\Controllers\Teacher\LeaveController as TeacherLeaveController;
use App\Http\Controllers\Sms\SmsController;
use App\Http\Controllers\Timetable\TimetableController;
use App\Http\Controllers\Pos\PosCategoryController;
use App\Http\Controllers\Pos\PosProductController;
use App\Http\Controllers\Pos\PosStockController;
use App\Http\Controllers\Pos\PosSaleController;
use App\Http\Controllers\Expense\ExpenseController;
use App\Http\Controllers\Expense\ExpenseCategoryController;
use App\Http\Controllers\Reports\ReportController;
use App\Services\Reports\ReportCatalog;
use App\Http\Controllers\ParentPortal\ParentAccountController;
use App\Http\Controllers\ParentPortal\ParentAuthController;
use App\Http\Controllers\ParentPortal\ParentDashboardController;
use App\Http\Controllers\ParentPortal\ParentChildController;
use App\Http\Controllers\ParentPortal\ParentAcademicsController;
use App\Http\Controllers\ParentPortal\ParentBillsController;
use App\Http\Controllers\ParentPortal\ParentPaymentsController;
use App\Http\Controllers\ParentPortal\ParentReportCardController;
use App\Http\Controllers\ParentPortal\ParentCommunicationsController;
use App\Http\Controllers\ParentPortal\ParentBillPaymentController;
use App\Http\Controllers\ParentPortal\AdminParentMessageController;

/* Parent Portal (separate auth) */
Route::prefix('parent')->group(function () {
    Route::get('login', [ParentAuthController::class, 'showLogin'])->name('parent.login');
    Route::post('login', [ParentAuthController::class, 'login'])->name('parent.login.process');
    Route::get('forgot-password', [ParentAuthController::class, 'showForgotPassword'])->name('parent.forgot-password');
    Route::post('forgot-password', [ParentAuthController::class, 'processForgotPassword'])->name('parent.forgot-password.process');

    Route::middleware(['auth:parent', 'parent'])->group(function () {
        Route::post('logout', [ParentAuthController::class, 'logout'])->name('parent.logout');
        Route::get('/', [ParentDashboardController::class, 'index'])->name('parent.dashboard');
        Route::get('account', [ParentAccountController::class, 'show'])->name('parent.account');
        Route::post('account/password', [ParentAccountController::class, 'updatePassword'])->name('parent.account.password');

        Route::post('messages', [ParentCommunicationsController::class, 'storeMessage'])->name('parent.messages.store');
        Route::get('communications', [ParentCommunicationsController::class, 'index'])->name('parent.communications');

        Route::middleware('parent.owns.student')->group(function () {
            Route::get('children/{student}', [ParentChildController::class, 'show'])->name('parent.child');
            Route::get('children/{student}/academics', [ParentAcademicsController::class, 'show'])->name('parent.academics');
            Route::get('children/{student}/bills', [ParentBillsController::class, 'index'])->name('parent.bills');
            Route::get('children/{student}/payments', [ParentPaymentsController::class, 'index'])->name('parent.payments');
            Route::get('children/{student}/payments/{payment}/receipt', [ParentPaymentsController::class, 'receipt'])->name('parent.payment.receipt');
            Route::get('children/{student}/report-card', [ParentReportCardController::class, 'index'])->name('parent.report-card');
            Route::get('children/{student}/report-card/print', [ParentReportCardController::class, 'print'])->name('parent.report-card.print');
            Route::get('children/{student}/communications', [ParentCommunicationsController::class, 'index'])->name('parent.communications.child');
            Route::post('children/{student}/pay-credit', [ParentBillPaymentController::class, 'applyCredit'])->name('parent.pay-credit');
            Route::post('children/{student}/paystack/initialize', [ParentBillPaymentController::class, 'initializePaystack'])->name('parent.paystack.initialize');
            Route::post('children/{student}/paystack/verify', [ParentBillPaymentController::class, 'verifyPaystack'])->name('parent.paystack.verify');
        });
    });
});
/* End Parent Portal */

Route::get('forgot-password',[AuthenticationController::class,'getForgetPassword'])->name('forgot-password');
Route::post('forgot-password-process',[AuthenticationController::class,'forgotPass'])->name('forgot-password-process');
Route::get('verify-otp-page/{id}',[AuthenticationController::class,'getOtp'])->name('verify-otp-page');
Route::post('update-user-photo-process',[AuthenticationController::class,'updatePhoto'])->name('update-user-photo-process');
Route::post('update-user-password-process',[AuthenticationController::class,'updatePassword'])->name('update-user-password-process');
Route::post('reset-otp-process/{id}',[AuthenticationController::class,'resetOtp'])->name('reset-otp-process');

/** End of Frontend */

/* Backend public auth */
Route::get('/', [AuthenticationController::class, 'getAdminLoginPage'])->name('admin-login');
Route::get('login', fn () => redirect('/'))->name('login');
Route::post('authentication-process',[AuthenticationController::class,'authenticationProcess'])->name('authentication-process');

Route::get('id-card/scan/student/{code}', [StudentController::class, 'scanByCode'])
    ->name('id-card-scan-student')
    ->where('code', '[A-Za-z0-9\-]+');
Route::get('id-card/scan/staff/{code}', [StaffController::class, 'scanByCode'])
    ->name('id-card-scan-staff')
    ->where('code', '[A-Za-z0-9\-]+');

Route::middleware(['staff.auth', 'menu.access'])->group(function () {
Route::get('dashboard',[DashboardController::class,'index'])->name('dashboard');
 
Route::get('user-profile',[AuthenticationController::class,'getUserProfile'])->name('user-profile');
Route::post('logout-authentication-process',[DashboardController::class,'logoutAuthenticationProcess'])->name('logout-authentication-process');
/* Authentication */ 

/* StaffController*/
   Route::controller(StaffController::class)->group(function () {
        Route::get('add-staff', 'getstaffView')->name('add-staff');
        Route::post('add-staff-process', 'addStaff')->name('add-staff-process');
        Route::get('list-staff', 'getListstaffView')->name('list-staff');
        Route::get('edit-staff/{id}', 'geteditStaffID')->name('edit-staff');
        Route::get('view-staff-details/{id}', 'getstaffDetailsView')->name('view-staff-details');
        Route::get('staff-id/{id}', 'getStaffID')->name('staff-id');
        Route::post('delete-staff-process', 'deleteStaff')->name('delete-staff-process');
        Route::post('add-staff-document-process', 'addDocStaff')->name('add-staff-document-process');
        Route::get('profile', 'getstaffprofileView')->name('profile');
        Route::post('update-staff-process/{id}', 'updateStaff')->name('update-staff-process');
        Route::post('update-photo-process', 'updatePhoto')->name('update-photo-process');
        Route::post('update-password-process', 'updatePassword')->name('update-password-process');
        
    });
/* End StaffController*/

/* HR Module */
   Route::controller(HrDashboardController::class)->group(function () {
        Route::get('hr-dashboard', 'index')->name('hr-dashboard');
    });

   Route::controller(DepartmentController::class)->group(function () {
        Route::get('hr-departments', 'index')->name('hr-departments');
        Route::post('hr-departments-process', 'store')->name('hr-departments-process');
        Route::get('hr-departments/{id}', 'show')->name('hr-departments-show');
        Route::post('hr-departments-update', 'update')->name('hr-departments-update');
    });

   Route::controller(PositionController::class)->group(function () {
        Route::get('hr-positions', 'index')->name('hr-positions');
        Route::post('hr-positions-process', 'store')->name('hr-positions-process');
        Route::get('hr-positions/{id}', 'show')->name('hr-positions-show');
        Route::post('hr-positions-update', 'update')->name('hr-positions-update');
        Route::post('hr-positions-delete', 'destroy')->name('hr-positions-delete');
    });

   Route::controller(LeaveController::class)->group(function () {
        Route::get('hr-leave', 'index')->name('hr-leave');
        Route::post('hr-leave-types-process', 'storeType')->name('hr-leave-types-process');
        Route::post('hr-leave-types-update', 'updateType')->name('hr-leave-types-update');
        Route::post('hr-leave-types-delete', 'destroyType')->name('hr-leave-types-delete');
        Route::post('hr-leave-requests-process', 'storeRequest')->name('hr-leave-requests-process');
        Route::post('hr-leave-review-process', 'review')->name('hr-leave-review-process');
    });

   Route::controller(HrAttendanceController::class)->group(function () {
        Route::get('hr-attendance', 'index')->name('hr-attendance');
        Route::post('hr-attendance-process', 'store')->name('hr-attendance-process');
    });

   Route::controller(SalaryStructureController::class)->group(function () {
        Route::get('hr-salary-structures', 'index')->name('hr-salary-structures');
        Route::post('hr-pay-grades-process', 'storeGrade')->name('hr-pay-grades-process');
        Route::post('hr-pay-grades-update', 'updateGrade')->name('hr-pay-grades-update');
        Route::post('hr-earning-types-process', 'storeEarning')->name('hr-earning-types-process');
        Route::post('hr-earning-types-update', 'updateEarning')->name('hr-earning-types-update');
        Route::post('hr-deduction-types-process', 'storeDeduction')->name('hr-deduction-types-process');
        Route::post('hr-deduction-types-update', 'updateDeduction')->name('hr-deduction-types-update');
    });

   Route::controller(PayrollController::class)->group(function () {
        Route::get('hr-payroll', 'index')->name('hr-payroll');
        Route::post('hr-payroll-generate', 'generate')->name('hr-payroll-generate');
        Route::get('hr-payroll/{id}', 'show')->name('hr-payroll-show');
        Route::post('hr-payroll/{id}/approve', 'approve')->name('hr-payroll-approve');
        Route::post('hr-payroll/{id}/paid', 'markPaid')->name('hr-payroll-paid');
        Route::get('hr-payslips', 'payslips')->name('hr-payslips');
        Route::get('hr-payslips/{id}/print', 'printPayslip')->name('hr-payslip-print');
    });

   Route::controller(HrSettingController::class)->group(function () {
        Route::get('hr-settings', 'index')->name('hr-settings');
        Route::post('hr-settings-process', 'update')->name('hr-settings-process');
    });

   Route::controller(AppraisalController::class)->group(function () {
        Route::get('hr-appraisals', 'index')->name('hr-appraisals');
        Route::post('hr-appraisals-process', 'store')->name('hr-appraisals-process');
        Route::get('hr-appraisals/{id}', 'show')->name('hr-appraisals-show');
    });
/* End HR Module */

/* UserManagementController*/
   Route::controller(UserManagementController::class)->group(function () {
        Route::get('user-categories', 'getUserCategoriesView')->name('user-categories');
        Route::post('add-user-category-process', 'addUserCategory')->name('add-user-category-process');
        Route::get('get-user-category-id/{id}', 'getUserCategoryId')->name('get-user-category-id');
        Route::post('update-user-category-process', 'updateUserCategory')->name('update-user-category-process');
        Route::post('delete-user-category-process', 'deleteUserCategory')->name('delete-user-category-process');
        Route::get('system-access', 'getSystemAccessView')->name('system-access');
        Route::post('system-access-process', 'saveSystemAccess')->name('system-access-process');
    });
/* End UserManagementController*/

/* StudentController*/
   Route::controller(StudentController::class)->group(function () {
        Route::get('add-student', 'getAddStudentView')->name('add-student');
        Route::post('add-student-process', 'addStudent')->name('add-student-process');
        Route::post('save-student-draft-process', 'saveStudentDraft')->name('save-student-draft-process');
        Route::get('list-students', 'getListStudentsView')->name('list-students');
        Route::get('edit-student/{id}', 'getEditStudentView')->name('edit-student');
        Route::post('update-student-process/{id}', 'updateStudent')->name('update-student-process');
        Route::get('view-student-details/{id}', 'getStudentDetailsView')->name('view-student-details');
        Route::get('print-student-details/{id}', 'printStudentDetailsView')->name('print-student-details');
        Route::get('student-profile/{id}', 'publicStudentProfileView')->name('public-student-profile')->middleware('signed');
    });

   Route::controller(AcademicYearController::class)->group(function () {
        Route::get('academic-years', 'index')->name('academic-years');
        Route::post('add-academic-year-process', 'store')->name('add-academic-year-process');
        Route::get('get-academic-year-id/{id}', 'show')->name('get-academic-year-id');
        Route::post('update-academic-year-process', 'update')->name('update-academic-year-process');
    });

   Route::controller(AcademicTermController::class)->group(function () {
        Route::get('academic-terms', 'index')->name('academic-terms');
        Route::post('add-academic-term-process', 'store')->name('add-academic-term-process');
        Route::get('get-academic-term-id/{id}', 'show')->name('get-academic-term-id');
        Route::post('update-academic-term-process', 'update')->name('update-academic-term-process');
    });

   Route::controller(AssessmentTypeController::class)->group(function () {
        Route::get('assessment-types', 'index')->name('assessment-types');
        Route::post('add-assessment-type-process', 'store')->name('add-assessment-type-process');
        Route::get('get-assessment-type-id/{id}', 'show')->name('get-assessment-type-id');
        Route::post('update-assessment-type-process', 'update')->name('update-assessment-type-process');
        Route::post('delete-assessment-type-process', 'destroy')->name('delete-assessment-type-process');
    });

   Route::controller(PromotionSettingController::class)->group(function () {
        Route::get('promotion-settings', 'index')->name('promotion-settings');
        Route::post('update-promotion-settings-process', 'update')->name('update-promotion-settings-process');
    });

   Route::controller(SchoolClassController::class)->group(function () {
        Route::get('school-classes', 'index')->name('school-classes');
        Route::post('add-school-class-process', 'store')->name('add-school-class-process');
        Route::get('get-school-class-id/{id}', 'show')->name('get-school-class-id');
        Route::post('update-school-class-process', 'update')->name('update-school-class-process');
    });

   Route::controller(ClassCategoryController::class)->group(function () {
        Route::get('class-categories', 'index')->name('class-categories');
        Route::post('add-class-category-process', 'store')->name('add-class-category-process');
        Route::get('get-class-category-id/{id}', 'show')->name('get-class-category-id');
        Route::post('update-class-category-process', 'update')->name('update-class-category-process');
    });

   Route::controller(ClassTeacherController::class)->group(function () {
        Route::get('class-teacher-assignment', 'index')->name('class-teacher-assignment');
        Route::get('get-class-teacher-assignment/{id}', 'show')->name('get-class-teacher-assignment');
        Route::post('assign-class-teacher-process', 'assign')->name('assign-class-teacher-process');
        Route::post('unassign-class-teacher-process', 'unassign')->name('unassign-class-teacher-process');
    });

   Route::controller(StudentClassAssignmentController::class)->group(function () {
        Route::get('student-class-assignment', 'index')->name('student-class-assignment');
        Route::get('student-class-assignment-search', 'search')->name('student-class-assignment-search');
        Route::get('get-student-class-assignment/{id}', 'show')->name('get-student-class-assignment');
        Route::get('student-class-assignment-preview', 'preview')->name('student-class-assignment-preview');
        Route::get('student-class-assignment-bulk-candidates', 'bulkCandidates')->name('student-class-assignment-bulk-candidates');
        Route::post('assign-student-class-process', 'assign')->name('assign-student-class-process');
        Route::post('assign-student-class-bulk-process', 'bulkAssign')->name('assign-student-class-bulk-process');
    });

   Route::controller(StudentRemarkController::class)->group(function () {
        Route::get('student-remarks', 'index')->name('student-remarks');
        Route::get('list-student-remarks', 'list')->name('list-student-remarks');
        Route::post('student-remarks-process', 'store')->name('student-remarks-process');
    });

   Route::controller(StudentPromotionController::class)->group(function () {
        Route::get('student-promotion', 'index')->name('student-promotion');
        Route::get('student-promotion/classes/{class}', 'show')->name('student-promotion-class');
        Route::post('student-promotion/classes/{class}/promote', 'promote')->name('student-promotion-process');
    });
/* End StudentController*/

/* SchoolSettingController*/
   Route::controller(SchoolSettingController::class)->group(function () {
        Route::get('school-settings', 'index')->name('school-settings');
        Route::get('academic-session', 'index')->name('academic-session');
        Route::post('update-school-settings-process', 'update')->name('update-school-settings-process');
    });
/* End SchoolSettingController*/

/* ID Card */
   Route::controller(CardSettingController::class)->group(function () {
        Route::get('id-card-settings', 'index')->name('id-card-settings');
        Route::post('id-card-settings-process', 'update')->name('id-card-settings-update');
    });
    Route::controller(PrintIdCardController::class)->group(function () {
        Route::get('print-id-cards', 'index')->name('print-id-cards');
        Route::get('print-id-cards-sheet', 'sheet')->name('print-id-cards-sheet');
    });
/* End ID Card */

/* DormitoryController*/
   Route::controller(DormitoryController::class)->group(function () {
        Route::get('dormitory-setup', 'index')->name('dormitory-setup');
        Route::post('add-house-process', 'storeHouse')->name('add-house-process');
        Route::get('get-house-id/{id}', 'showHouse')->name('get-house-id');
        Route::post('update-house-process', 'updateHouse')->name('update-house-process');
        Route::post('add-dormitory-process', 'storeDormitory')->name('add-dormitory-process');
        Route::get('get-dormitory-id/{id}', 'showDormitory')->name('get-dormitory-id');
        Route::post('update-dormitory-process', 'updateDormitory')->name('update-dormitory-process');
        Route::get('get-dormitories-by-house/{houseId}', 'dormitoriesByHouse')->name('get-dormitories-by-house');
        Route::get('get-available-beds/{dormitoryId}', 'availableBeds')->name('get-available-beds');
        Route::get('get-student-dormitory/{id}', 'getStudentAssignment')->name('get-student-dormitory');
        Route::post('assign-student-dormitory-process', 'assignStudent')->name('assign-student-dormitory-process');
        Route::post('unassign-student-dormitory-process', 'unassignStudent')->name('unassign-student-dormitory-process');
    });
/* End DormitoryController*/

/* CourseController*/
   Route::controller(CourseController::class)->group(function () {
        Route::get('add-course', 'getAddCourseView')->name('add-course');
        Route::post('add-course-process', 'addCourse')->name('add-course-process');
        Route::post('add-sub-course-process', 'storeSubCourse')->name('add-sub-course-process');
        Route::get('get-course-id/{id}', 'getCourseID')->name('get-course-id');
        Route::post('update-course-process', 'updateCourse')->name('update-course-process');
    });

   Route::controller(CourseTeacherController::class)->group(function () {
        Route::get('course-teacher-assignment', 'index')->name('course-teacher-assignment');
        Route::get('get-course-teacher-assignment/{id}', 'show')->name('get-course-teacher-assignment');
        Route::post('assign-course-teacher-process', 'assign')->name('assign-course-teacher-process');
        Route::post('unassign-course-teacher-process', 'unassign')->name('unassign-course-teacher-process');
    });

   Route::controller(CourseRegistrationController::class)->group(function () {
        Route::get('course-registration', 'index')->name('course-registration');
        Route::get('course-registration-courses', 'courses')->name('course-registration-courses');
        Route::get('course-registration-registered', 'registered')->name('course-registration-registered');
        Route::post('course-registration-register', 'register')->name('course-registration-register');
        Route::post('course-registration-unregister', 'unregister')->name('course-registration-unregister');
    });
/* End CourseController*/

/* Billing */
   Route::controller(BillingItemController::class)->group(function () {
        Route::get('billing-items', 'index')->name('billing-items');
        Route::post('add-billing-item-process', 'store')->name('add-billing-item-process');
        Route::get('get-billing-item-id/{id}', 'show')->name('get-billing-item-id');
        Route::post('update-billing-item-process', 'update')->name('update-billing-item-process');
    });

   Route::controller(CategoryBillSetupController::class)->group(function () {
        Route::get('category-bill-setup', 'index')->name('category-bill-setup');
        Route::get('category-bill-setup-load', 'load')->name('category-bill-setup-load');
        Route::post('category-bill-setup-save', 'store')->name('category-bill-setup-save');
    });

   Route::controller(StudentBillController::class)->group(function () {
        Route::get('student-bills', 'index')->name('student-bills');
        Route::get('student-bills/print', 'printLedger')->name('student-bills-print');
        Route::get('student-bills/print/{id}', 'printStatement')->name('student-bill-print');
        Route::get('print-bills', 'printBillsIndex')->name('print-bills');
        Route::get('print-class-bills/output', 'printClassStatements')->name('print-class-bills-output');
        Route::get('edit-student-bills', 'editIndex')->name('edit-student-bills');
        Route::get('edit-student-bills-search', 'search')->name('edit-student-bills-search');
        Route::get('get-student-bills/{id}', 'show')->name('get-student-bills');
        Route::get('get-student-outstanding-bills/{id}', 'outstanding')->name('get-student-outstanding-bills');
        Route::post('update-student-bill-process', 'updateBill')->name('update-student-bill-process');
    });

   Route::controller(BillPaymentController::class)->group(function () {
        Route::get('record-bill-payment/{id}', 'cashier')->name('record-bill-payment');
        Route::post('record-bill-payment-process', 'store')->name('record-bill-payment-process');
        Route::get('pending-bank-payments', 'pendingBankPayments')->name('pending-bank-payments');
        Route::post('pending-bank-payments/{pending}/approve', 'approvePendingBankPayment')->name('pending-bank-payments-approve');
        Route::post('pending-bank-payments/{pending}/reject', 'rejectPendingBankPayment')->name('pending-bank-payments-reject');
        Route::post('paystack/bill-payment/initialize', 'initializePaystack')->name('paystack-bill-payment-initialize');
        Route::post('paystack/bill-payment/verify', 'verifyPaystack')->name('paystack-bill-payment-verify');
        Route::post('paystack/webhook', 'webhook')->name('paystack-webhook')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
        Route::get('bill-payment-receipt/{id}', 'receipt')->name('bill-payment-receipt');
    });
/* End Billing */

/* Teacher Management */
   Route::controller(TeacherDirectoryController::class)->group(function () {
        Route::get('teacher-directory', 'index')->name('teacher-directory');
    });

   Route::controller(GradingSchemeController::class)->group(function () {
        Route::get('grading-scheme', 'index')->name('grading-scheme');
        Route::post('add-grading-scheme-process', 'store')->name('add-grading-scheme-process');
        Route::post('update-grading-scheme-process', 'update')->name('update-grading-scheme-process');
        Route::post('delete-grading-scheme-process', 'destroy')->name('delete-grading-scheme-process');
    });
/* End Teacher Management */

/* Teacher Portal */
   Route::controller(TeacherDashboardController::class)->group(function () {
        Route::get('teacher-dashboard', 'index')->name('teacher-dashboard');
    });

   Route::controller(ClassWorkspaceController::class)->group(function () {
        Route::get('teacher/classes/{class}', 'show')->name('teacher-class-workspace');
    });

   Route::controller(CourseWorkspaceController::class)->group(function () {
        Route::get('teacher/courses/{course}/classes/{class}', 'show')->name('teacher-course-workspace');
    });

   Route::controller(AssessmentController::class)->group(function () {
        Route::get('teacher-assessments', 'hub')->name('teacher-assessments');
        Route::get('teacher-assessment-records', 'records')->name('teacher-assessment-records');
        Route::get('teacher/assessment-setup-options', 'setupOptions')->name('teacher-assessment-setup-options');
        Route::get('teacher/classes/{class}/assessments', 'classIndex')->name('teacher-class-assessments');
        Route::get('teacher/classes/{class}/assessment-records', 'classRecords')->name('teacher-class-assessment-records');
        Route::get('teacher/classes/{class}/assessment-marks', 'classMarks')->name('teacher-class-assessment-marks');
        Route::get('teacher/courses/{course}/classes/{class}/assessments', 'courseIndex')->name('teacher-course-assessments');
        Route::get('teacher/courses/{course}/classes/{class}/assessment-records', 'courseRecords')->name('teacher-course-assessment-records');
        Route::get('teacher/courses/{course}/classes/{class}/assessment-marks', 'courseMarks')->name('teacher-course-assessment-marks');
        Route::post('teacher/courses/{course}/classes/{class}/assessment-marks', 'saveCourseMarks')->name('teacher-course-assessment-marks-process');
        Route::post('teacher-assessments-process', 'store')->name('teacher-assessments-process');
        Route::get('teacher/assessments/{assessment}/scores', 'scores')->name('teacher-assessment-scores');
        Route::post('teacher/assessments/{assessment}/scores', 'saveScores')->name('teacher-assessment-scores-process');
        Route::delete('teacher/assessments/{assessment}', 'destroy')->name('teacher-assessments-delete');
    });

   Route::controller(AttendanceController::class)->group(function () {
        Route::get('teacher-attendance', 'hub')->name('teacher-attendance');
        Route::get('teacher/classes/{class}/attendance', 'index')->name('teacher-class-attendance');
        Route::post('teacher/classes/{class}/attendance', 'store')->name('teacher-class-attendance-process');
    });

   Route::controller(GradebookController::class)->group(function () {
        Route::get('teacher-gradebook', 'hub')->name('teacher-gradebook');
        Route::get('teacher/classes/{class}/gradebook', 'index')->name('teacher-class-gradebook');
        Route::get('teacher/students/{student}/report-card/print', 'printReportCard')->name('teacher-report-card-print');
        Route::get('teacher/classes/{class}/report-cards/print', 'printClassReportCards')->name('teacher-class-report-cards-print');
    });

   Route::redirect('teacher-leave', '/leave-application')->name('teacher-leave');

   Route::controller(TeacherLeaveController::class)->group(function () {
        Route::get('leave-application', 'index')->name('leave-application');
        Route::post('leave-application-process', 'store')->name('leave-application-process');
    });
/* End Teacher Portal */

/* Send SMS */
   Route::controller(SmsController::class)->group(function () {
        Route::get('send-sms', 'index')->name('send-sms');
        Route::get('send-sms-recipients', 'recipients')->name('send-sms-recipients');
        Route::post('send-sms-process', 'send')->name('send-sms-process');
    });

   Route::controller(AdminParentMessageController::class)->group(function () {
        Route::get('parent-messages', 'index')->name('parent-messages');
        Route::post('parent-messages/{message}/read', 'markRead')->name('parent-messages-read');
    });
/* End Send SMS */

/* Timetable */
   Route::controller(TimetableController::class)->group(function () {
        Route::get('timetable', 'index')->name('timetable');
        Route::get('timetable-periods', 'periods')->name('timetable-periods');
        Route::post('timetable-periods-process', 'updatePeriods')->name('timetable-periods-process');
        Route::post('timetable-generate', 'generate')->name('timetable-generate');
        Route::get('timetable/{class}/print', 'print')->name('timetable-print');
        Route::get('timetable/{class}', 'show')->name('timetable-show');
    });
/* End Timetable */

/* POS */
   Route::controller(PosSaleController::class)->group(function () {
        Route::get('pos-sale', 'index')->name('pos-sale');
        Route::post('pos-sale-process', 'store')->name('pos-sale-process');
        Route::post('paystack/pos-sale/initialize', 'initializePaystack')->name('paystack-pos-sale-initialize');
        Route::post('paystack/pos-sale/verify', 'verifyPaystack')->name('paystack-pos-sale-verify');
        Route::get('pos-sales', 'history')->name('pos-sales');
        Route::get('pos-receipt/{id}', 'receipt')->name('pos-receipt');
        Route::get('pos-student-search', 'searchStudents')->name('pos-student-search');
    });

   Route::controller(PosProductController::class)->group(function () {
        Route::get('pos-products', 'index')->name('pos-products');
        Route::post('add-pos-product-process', 'store')->name('add-pos-product-process');
        Route::get('get-pos-product-id/{id}', 'show')->name('get-pos-product-id');
        Route::post('update-pos-product-process', 'update')->name('update-pos-product-process');
    });

   Route::controller(PosCategoryController::class)->group(function () {
        Route::get('pos-categories', 'index')->name('pos-categories');
        Route::post('add-pos-category-process', 'store')->name('add-pos-category-process');
        Route::get('get-pos-category-id/{id}', 'show')->name('get-pos-category-id');
        Route::post('update-pos-category-process', 'update')->name('update-pos-category-process');
        Route::post('delete-pos-category-process', 'destroy')->name('delete-pos-category-process');
    });

   Route::controller(PosStockController::class)->group(function () {
        Route::get('pos-stock', 'index')->name('pos-stock');
        Route::post('pos-stock-process', 'store')->name('pos-stock-process');
    });
/* End POS */

/* Expenses */
   Route::controller(ExpenseController::class)->group(function () {
        Route::get('expenses', 'index')->name('expenses');
        Route::post('add-expense-process', 'store')->name('add-expense-process');
        Route::get('get-expense-id/{id}', 'show')->name('get-expense-id');
        Route::post('update-expense-process', 'update')->name('update-expense-process');
        Route::post('delete-expense-process', 'destroy')->name('delete-expense-process');
    });

   Route::controller(ExpenseCategoryController::class)->group(function () {
        Route::get('expense-categories', 'index')->name('expense-categories');
        Route::post('add-expense-category-process', 'store')->name('add-expense-category-process');
        Route::get('get-expense-category-id/{id}', 'show')->name('get-expense-category-id');
        Route::post('update-expense-category-process', 'update')->name('update-expense-category-process');
    });
/* End Expenses */

/* Reports */
    foreach (ReportCatalog::KEYS as $reportKey => $reportTitle) {
        $reportUrl = ReportCatalog::url($reportKey);
        Route::get($reportUrl, [ReportController::class, 'show'])->defaults('key', $reportKey)->name($reportUrl);
        Route::get($reportUrl.'-print', [ReportController::class, 'print'])->defaults('key', $reportKey)->name($reportUrl.'-print');
        Route::get($reportUrl.'-pdf', [ReportController::class, 'pdf'])->defaults('key', $reportKey)->name($reportUrl.'-pdf');
        Route::get($reportUrl.'-excel', [ReportController::class, 'excel'])->defaults('key', $reportKey)->name($reportUrl.'-excel');
    }
/* End Reports */
});
