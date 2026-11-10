<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserAccessService
{
    /** @var array<int, list<string>> */
    private array $urlCache = [];

    /** @var array<int, Collection<int, int>> */
    private array $linkIdCache = [];

    /** @var list<string>|null */
    private ?array $menuUrls = null;

    /**
     * Assigned screen ids, plus the parent menu so the sidebar group still appears.
     *
     * @return Collection<int, int>
     */
    public function linkIds(?User $user = null): Collection
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return collect();
        }

        $userId = (int) $user->id;
        if (isset($this->linkIdCache[$userId])) {
            return $this->linkIdCache[$userId];
        }

        $linkIds = DB::table('user_access_links')
            ->where('user_id', $userId)
            ->pluck('link_id');

        $categoryId = (int) ($user->user_cat ?: $user->cat_id);
        if ($categoryId) {
            $categoryLinkIds = DB::table('user_cat_links')
                ->where('cat_id', $categoryId)
                ->pluck('link_id');
            $linkIds = $linkIds->merge($categoryLinkIds);
        }

        $leaveLinkId = DB::table('user_links')
            ->where('link_url', 'leave-application')
            ->where('status', 'Active')
            ->value('link_id');

        if ($leaveLinkId) {
            $linkIds = $linkIds->push((int) $leaveLinkId);
        }

        if ($linkIds->isEmpty()) {
            return $this->linkIdCache[$userId] = collect();
        }

        $parentIds = DB::table('user_links')
            ->whereIn('link_id', $linkIds)
            ->where('link_parent', '>', 0)
            ->pluck('link_parent');

        return $this->linkIdCache[$userId] = $linkIds->merge($parentIds)->unique()->values();
    }

    /**
     * Sidebar rows for the signed-in account.
     */
    public function menuLinks(?User $user = null): Collection
    {
        $linkIds = $this->linkIds($user);

        if ($linkIds->isEmpty()) {
            return collect();
        }

        return DB::table('user_links')
            ->whereIn('link_id', $linkIds)
            ->where('status', 'Active')
            ->orderBy('link_name')
            ->get([
                'link_id',
                'page_id',
                'page_id_sub',
                'link_url',
                'link_name',
                'link_image',
                'link_parent',
            ]);
    }

    /**
     * @return list<string>
     */
    public function urls(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        if (! $user) {
            return [];
        }

        $userId = (int) $user->id;
        if (isset($this->urlCache[$userId])) {
            return $this->urlCache[$userId];
        }

        $linkIds = $this->linkIds($user);
        if ($linkIds->isEmpty()) {
            return $this->urlCache[$userId] = [];
        }

        $urls = DB::table('user_links')
            ->whereIn('link_id', $linkIds)
            ->where('status', 'Active')
            ->whereNotNull('link_url')
            ->where('link_url', '!=', '')
            ->pluck('link_url')
            ->unique()
            ->values()
            ->all();

        return $this->urlCache[$userId] = $urls;
    }

    public function can(string $routeName, ?User $user = null): bool
    {
        return in_array($routeName, $this->urls($user), true);
    }

    /**
     * @param  list<string>  $routeNames
     */
    public function canAny(array $routeNames, ?User $user = null): bool
    {
        foreach ($routeNames as $routeName) {
            if ($this->can($routeName, $user)) {
                return true;
            }
        }

        return false;
    }

    public function allowsRoute(?string $routeName, ?User $user = null): bool
    {
        if ($routeName === null || $routeName === '') {
            return false;
        }

        if (in_array($routeName, $this->openRoutes(), true)) {
            return true;
        }

        if ($this->can($routeName, $user)) {
            return true;
        }

        foreach ($this->companions()[$routeName] ?? [] as $menu) {
            if ($this->can($menu, $user)) {
                return true;
            }
        }

        $owner = $this->longestMenuUrl($routeName);

        return $owner !== null && $this->can($owner, $user);
    }

    /**
     * @return list<string>
     */
    private function openRoutes(): array
    {
        return [
            'dashboard',
            'user-profile',
            'logout-authentication-process',
            'profile',
            'update-photo-process',
            'update-password-process',
            'leave-application',
            'leave-application-process',
            'teacher-leave',
        ];
    }

    /**
     * Actions that do not share their menu's route name.
     * Teacher portal also opens the roster, attendance, assessment, and gradebook pages.
     *
     * @return array<string, list<string>>
     */
    private function companions(): array
    {
        return [
            'edit-staff' => ['list-staff'],
            'view-staff-details' => ['list-staff'],
            'staff-id' => ['list-staff'],
            'delete-staff-process' => ['list-staff'],
            'update-staff-process' => ['list-staff'],
            'add-staff-document-process' => ['list-staff', 'add-staff'],

            'hr-pay-grades-process' => ['hr-salary-structures'],
            'hr-pay-grades-update' => ['hr-salary-structures'],
            'hr-earning-types-process' => ['hr-salary-structures'],
            'hr-earning-types-update' => ['hr-salary-structures'],
            'hr-deduction-types-process' => ['hr-salary-structures'],
            'hr-deduction-types-update' => ['hr-salary-structures'],
            'hr-payslip-print' => ['hr-payslips'],

            'add-user-category-process' => ['user-categories'],
            'get-user-category-id' => ['user-categories'],
            'update-user-category-process' => ['user-categories'],
            'delete-user-category-process' => ['user-categories'],

            'parent-accounts-student-search' => ['parent-accounts'],
            'parent-accounts-process' => ['parent-accounts'],
            'parent-accounts-reset-process' => ['parent-accounts'],

            'save-student-draft-process' => ['add-student'],
            'download-student-bulk-template' => ['add-student'],
            'bulk-import-students-process' => ['add-student'],
            'edit-student' => ['list-students'],
            'update-student-process' => ['list-students'],
            'view-student-details' => ['list-students'],
            'print-student-details' => ['list-students'],
            'public-student-profile' => ['list-students'],

            'add-academic-year-process' => ['academic-years'],
            'get-academic-year-id' => ['academic-years'],
            'update-academic-year-process' => ['academic-years'],
            'add-academic-term-process' => ['academic-terms'],
            'get-academic-term-id' => ['academic-terms'],
            'update-academic-term-process' => ['academic-terms'],
            'add-assessment-type-process' => ['assessment-types'],
            'get-assessment-type-id' => ['assessment-types'],
            'update-assessment-type-process' => ['assessment-types'],
            'delete-assessment-type-process' => ['assessment-types'],
            'update-promotion-settings-process' => ['promotion-settings'],

            'add-school-class-process' => ['school-classes'],
            'get-school-class-id' => ['school-classes'],
            'update-school-class-process' => ['school-classes'],
            'add-class-category-process' => ['class-categories'],
            'get-class-category-id' => ['class-categories'],
            'update-class-category-process' => ['class-categories'],
            'get-class-teacher-assignment' => ['class-teacher-assignment'],
            'assign-class-teacher-process' => ['class-teacher-assignment'],
            'unassign-class-teacher-process' => ['class-teacher-assignment'],
            'get-student-class-assignment' => ['student-class-assignment'],
            'assign-student-class-process' => ['student-class-assignment'],
            'assign-student-class-bulk-process' => ['student-class-assignment'],

            'update-school-settings-process' => ['school-settings', 'academic-session'],

            'add-house-process' => ['dormitory-setup'],
            'get-house-id' => ['dormitory-setup'],
            'update-house-process' => ['dormitory-setup'],
            'add-dormitory-process' => ['dormitory-setup'],
            'get-dormitory-id' => ['dormitory-setup'],
            'update-dormitory-process' => ['dormitory-setup'],
            'get-dormitories-by-house' => ['dormitory-setup'],
            'get-available-beds' => ['dormitory-setup'],
            'get-student-dormitory' => ['dormitory-setup'],
            'assign-student-dormitory-process' => ['dormitory-setup'],
            'unassign-student-dormitory-process' => ['dormitory-setup'],

            'add-sub-course-process' => ['add-course'],
            'get-course-id' => ['add-course'],
            'update-course-process' => ['add-course'],
            'get-course-teacher-assignment' => ['course-teacher-assignment'],
            'assign-course-teacher-process' => ['course-teacher-assignment'],
            'unassign-course-teacher-process' => ['course-teacher-assignment'],

            'add-billing-item-process' => ['billing-items'],
            'get-billing-item-id' => ['billing-items'],
            'update-billing-item-process' => ['billing-items'],
            'student-bill-print' => ['student-bills', 'print-bills'],
            'print-class-bills-output' => ['print-bills'],
            'get-student-bills' => ['edit-student-bills'],
            'get-student-outstanding-bills' => ['student-bills'],
            'update-student-bill-process' => ['edit-student-bills'],
            'record-bill-payment' => ['student-bills'],
            'record-bill-payment-process' => ['student-bills'],
            'bill-payment-receipt' => ['student-bills'],
            'paystack-bill-payment-initialize' => ['student-bills'],
            'paystack-bill-payment-verify' => ['student-bills'],
            'paystack-webhook' => ['student-bills'],

            'add-grading-scheme-process' => ['grading-scheme'],
            'update-grading-scheme-process' => ['grading-scheme'],
            'delete-grading-scheme-process' => ['grading-scheme'],

            'teacher-class-workspace' => ['teacher-dashboard'],
            'teacher-course-workspace' => ['teacher-dashboard'],
            'teacher-assessment-setup-options' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-class-assessments' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-class-assessment-records' => ['teacher-assessments', 'teacher-assessment-records', 'teacher-dashboard'],
            'teacher-class-assessment-marks' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-course-assessments' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-course-assessment-records' => ['teacher-assessments', 'teacher-assessment-records', 'teacher-dashboard'],
            'teacher-course-assessment-marks' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-course-assessment-marks-process' => ['teacher-assessments', 'teacher-dashboard'],
            'teacher-assessments-process' => ['teacher-dashboard'],
            'teacher-assessments-delete' => ['teacher-dashboard'],
            'teacher-assessment-scores' => ['teacher-assessments', 'teacher-assessment-records', 'teacher-dashboard'],
            'teacher-assessment-scores-process' => ['teacher-assessments', 'teacher-assessment-records', 'teacher-dashboard'],
            'teacher-class-attendance' => ['teacher-attendance', 'teacher-dashboard'],
            'teacher-class-attendance-process' => ['teacher-attendance', 'teacher-dashboard'],
            'teacher-class-gradebook' => ['teacher-gradebook', 'teacher-dashboard'],
            'teacher-report-card-print' => ['teacher-gradebook', 'teacher-dashboard'],
            'teacher-class-report-cards-print' => ['teacher-gradebook', 'teacher-dashboard'],

            'paystack-pos-sale-initialize' => ['pos-sale'],
            'paystack-pos-sale-verify' => ['pos-sale'],
            'pos-receipt' => ['pos-sale', 'pos-sales'],
            'pos-student-search' => ['pos-sale'],
            'add-pos-product-process' => ['pos-products'],
            'get-pos-product-id' => ['pos-products'],
            'update-pos-product-process' => ['pos-products'],
            'add-pos-category-process' => ['pos-categories'],
            'get-pos-category-id' => ['pos-categories'],
            'update-pos-category-process' => ['pos-categories'],
            'delete-pos-category-process' => ['pos-categories'],

            'add-expense-process' => ['expenses'],
            'get-expense-id' => ['expenses'],
            'update-expense-process' => ['expenses'],
            'delete-expense-process' => ['expenses'],
            'add-expense-category-process' => ['expense-categories'],
            'get-expense-category-id' => ['expense-categories'],
            'update-expense-category-process' => ['expense-categories'],
        ];
    }

    private function longestMenuUrl(string $routeName): ?string
    {
        $best = null;

        foreach ($this->menuUrls() as $url) {
            if ($routeName !== $url && ! str_starts_with($routeName, $url.'-')) {
                continue;
            }

            if ($best === null || strlen($url) > strlen($best)) {
                $best = $url;
            }
        }

        return $best;
    }

    /**
     * @return list<string>
     */
    private function menuUrls(): array
    {
        if ($this->menuUrls !== null) {
            return $this->menuUrls;
        }

        $this->menuUrls = DB::table('user_links')
            ->where('status', 'Active')
            ->whereNotNull('link_url')
            ->whereNotIn('link_url', ['', '#'])
            ->pluck('link_url')
            ->unique()
            ->values()
            ->all();

        return $this->menuUrls;
    }
}
