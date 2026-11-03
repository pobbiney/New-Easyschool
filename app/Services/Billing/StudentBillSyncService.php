<?php

namespace App\Services\Billing;

use App\Models\CategoryBillSetup;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Collection;

class StudentBillSyncService
{
    public function syncForSetup(CategoryBillSetup $setup): array
    {
        $setup->load('items');

        $students = $this->matchingStudents($setup);
        $created = 0;
        $updated = 0;

        foreach ($students as $student) {
            foreach ($setup->items as $item) {
                if ((float) $item->amount <= 0) {
                    continue;
                }

                $wasExisting = $this->upsertStudentBill(
                    $student,
                    $setup,
                    $item->billing_item_id,
                    (float) $item->amount
                );

                $wasExisting ? $updated++ : $created++;
            }
        }

        return [
            'students_matched' => $students->count(),
            'bills_created' => $created,
            'bills_updated' => $updated,
        ];
    }

    public function syncForStudent(Student $student): array
    {
        $student->load('schoolClass.category');

        if (! $student->school_class_id || ! $student->academic_year_id || ! $student->academic_term_id) {
            return ['students_matched' => 0, 'bills_created' => 0, 'bills_updated' => 0];
        }

        $categoryId = $student->schoolClass?->class_category_id;

        if (! $categoryId) {
            return ['students_matched' => 0, 'bills_created' => 0, 'bills_updated' => 0];
        }

        $setup = CategoryBillSetup::query()
            ->where('class_category_id', $categoryId)
            ->where('academic_year_id', $student->academic_year_id)
            ->where('academic_term_id', $student->academic_term_id)
            ->where('status', 'Active')
            ->with('items')
            ->first();

        if (! $setup) {
            return ['students_matched' => 1, 'bills_created' => 0, 'bills_updated' => 0];
        }

        $created = 0;
        $updated = 0;

        foreach ($setup->items as $item) {
            if ((float) $item->amount <= 0) {
                continue;
            }

            $wasExisting = $this->upsertStudentBill(
                $student,
                $setup,
                $item->billing_item_id,
                (float) $item->amount
            );

            $wasExisting ? $updated++ : $created++;
        }

        return [
            'students_matched' => 1,
            'bills_created' => $created,
            'bills_updated' => $updated,
        ];
    }

    public function previewSetupForEnrollment(int $schoolClassId, int $academicYearId, int $academicTermId): array
    {
        $schoolClass = SchoolClass::with('category')->find($schoolClassId);

        if (! $schoolClass || ! $schoolClass->class_category_id) {
            return [
                'setup_found' => false,
                'category_name' => $schoolClass?->category?->name,
                'items' => [],
                'total' => 0,
                'message' => 'Selected class has no category assigned.',
            ];
        }

        $setup = CategoryBillSetup::query()
            ->where('class_category_id', $schoolClass->class_category_id)
            ->where('academic_year_id', $academicYearId)
            ->where('academic_term_id', $academicTermId)
            ->where('status', 'Active')
            ->with(['items.billingItem'])
            ->first();

        if (! $setup) {
            return [
                'setup_found' => false,
                'category_name' => $schoolClass->category?->name,
                'items' => [],
                'total' => 0,
                'message' => 'No bill setup found for this category, year, and term.',
            ];
        }

        $items = $setup->items
            ->filter(fn ($item) => (float) $item->amount > 0)
            ->map(fn ($item) => [
                'name' => $item->billingItem?->name,
                'amount' => (float) $item->amount,
                'is_compulsory' => (bool) $item->billingItem?->is_compulsory,
            ])
            ->values();

        return [
            'setup_found' => $items->isNotEmpty(),
            'category_name' => $schoolClass->category?->name,
            'items' => $items,
            'total' => $items->sum('amount'),
            'message' => $items->isEmpty()
                ? 'Bill setup exists but has no billable items.'
                : null,
        ];
    }

    private function upsertStudentBill(Student $student, CategoryBillSetup $setup, int $billingItemId, float $amountDue): bool
    {
        $bill = StudentBill::firstOrNew([
            'student_id' => $student->id,
            'billing_item_id' => $billingItemId,
            'academic_year_id' => $setup->academic_year_id,
            'academic_term_id' => $setup->academic_term_id,
        ]);

        $wasExisting = $bill->exists;

        $bill->category_bill_setup_id = $setup->id;
        $bill->amount_due = $amountDue;
        $bill->amount_paid = $bill->amount_paid ?? 0;
        $bill->refreshTotals();
        $bill->save();

        return $wasExisting;
    }

    private function matchingStudents(CategoryBillSetup $setup): Collection
    {
        return Student::query()
            ->where('status', 'Active')
            ->where('academic_year_id', $setup->academic_year_id)
            ->where('academic_term_id', $setup->academic_term_id)
            ->whereHas('schoolClass', function ($query) use ($setup) {
                $query->where('class_category_id', $setup->class_category_id);
            })
            ->get();
    }
}
