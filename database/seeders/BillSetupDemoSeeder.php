<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\BillingItem;
use App\Models\CategoryBillSetup;
use App\Models\CategoryBillSetupItem;
use App\Models\ClassCategory;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\Billing\StudentBillSyncService;
use Illuminate\Database\Seeder;

class BillSetupDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;

        $categories = $this->seedClassCategories($adminId);
        $this->assignClassesToCategories($categories, $adminId);

        $billingItems = $this->seedBillingItems($adminId);

        $academicYears = AcademicYear::query()
            ->whereIn('name', ['2025/2026', '2027/2028'])
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        if ($academicYears->isEmpty()) {
            $fallback = AcademicYear::where('status', 'Active')->orderByDesc('name')->first();
            $academicYears = $fallback ? collect([$fallback]) : collect();
        }

        $firstTerm = AcademicTerm::where('name', 'First Term')->first()
            ?? AcademicTerm::orderBy('sort_order')->first();
        $secondTerm = AcademicTerm::where('name', 'Second Term')->first();

        if ($academicYears->isEmpty() || ! $firstTerm) {
            $this->command?->warn('Academic year or term not found. Run academic migrations first.');

            return;
        }

        $setupPlans = [];

        foreach ($academicYears as $academicYear) {
            $setupPlans[] = [
                'category' => 'Primary',
                'year' => $academicYear,
                'term' => $firstTerm,
                'amounts' => [
                    'Tuition' => 1200.00,
                    'PTA Levy' => 50.00,
                    'Transport' => 150.00,
                    'ICT Levy' => 75.00,
                ],
            ];

            if ($secondTerm) {
                $setupPlans[] = [
                    'category' => 'Primary',
                    'year' => $academicYear,
                    'term' => $secondTerm,
                    'amounts' => [
                        'Tuition' => 1200.00,
                        'PTA Levy' => 50.00,
                        'Transport' => 150.00,
                    ],
                ];
            }

            $setupPlans[] = [
                'category' => 'JHS',
                'year' => $academicYear,
                'term' => $firstTerm,
                'amounts' => [
                    'Tuition' => 1500.00,
                    'PTA Levy' => 60.00,
                    'Examination Fee' => 120.00,
                    'ICT Levy' => 90.00,
                ],
            ];
        }

        $syncService = app(StudentBillSyncService::class);
        $totalSynced = 0;

        foreach ($setupPlans as $plan) {
            if (! $plan['term']) {
                continue;
            }

            $category = $categories->get($plan['category']);

            if (! $category) {
                continue;
            }

            $setup = CategoryBillSetup::firstOrCreate(
                [
                    'class_category_id' => $category->id,
                    'academic_year_id' => $plan['year']->id,
                    'academic_term_id' => $plan['term']->id,
                ],
                [
                    'status' => 'Active',
                    'created_by' => $adminId,
                ]
            );

            $setup->update(['updated_by' => $adminId, 'status' => 'Active']);

            $savedItemIds = [];

            foreach ($plan['amounts'] as $itemName => $amount) {
                $billingItem = $billingItems->get($itemName);

                if (! $billingItem) {
                    continue;
                }

                CategoryBillSetupItem::updateOrCreate(
                    [
                        'category_bill_setup_id' => $setup->id,
                        'billing_item_id' => $billingItem->id,
                    ],
                    ['amount' => $amount]
                );

                $savedItemIds[] = $billingItem->id;
            }

            CategoryBillSetupItem::query()
                ->where('category_bill_setup_id', $setup->id)
                ->whereNotIn('billing_item_id', $savedItemIds)
                ->delete();

            $stats = $syncService->syncForSetup($setup->fresh('items'));
            $totalSynced += $stats['bills_created'] + $stats['bills_updated'];

            $this->command?->info(sprintf(
                'Seeded %s / %s / %s — %d students, %d bills synced.',
                $plan['category'],
                $plan['year']->name,
                $plan['term']->name,
                $stats['students_matched'],
                $stats['bills_created'] + $stats['bills_updated']
            ));
        }

        $this->command?->info("Bill setup demo complete. {$totalSynced} student bill lines synced.");
    }

    private function seedClassCategories(int $adminId)
    {
        $definitions = [
            [
                'name' => 'Primary',
                'description' => 'Primary school classes from Primary 1 to Primary 6.',
            ],
            [
                'name' => 'JHS',
                'description' => 'Junior High School classes.',
            ],
        ];

        return collect($definitions)->mapWithKeys(function (array $definition) use ($adminId) {
            $category = ClassCategory::firstOrCreate(
                ['name' => $definition['name']],
                [
                    'description' => $definition['description'],
                    'status' => 'Active',
                    'created_by' => $adminId,
                ]
            );

            return [$definition['name'] => $category];
        });
    }

    private function assignClassesToCategories($categories, int $adminId): void
    {
        $classMap = [
            'Primary' => ['Primary 1', 'Primary 2', 'Primary 3'],
            'JHS' => ['JHS 1', 'JHS 2'],
        ];

        foreach ($classMap as $categoryName => $classNames) {
            $category = $categories->get($categoryName);

            if (! $category) {
                continue;
            }

            foreach ($classNames as $className) {
                SchoolClass::where('name', $className)->update([
                    'class_category_id' => $category->id,
                    'updated_by' => $adminId,
                ]);
            }
        }
    }

    private function seedBillingItems(int $adminId)
    {
        $items = [
            [
                'name' => 'Tuition',
                'description' => 'Core tuition fee for the term.',
                'is_compulsory' => true,
            ],
            [
                'name' => 'PTA Levy',
                'description' => 'Parent-Teacher Association contribution.',
                'is_compulsory' => false,
            ],
            [
                'name' => 'Transport',
                'description' => 'School bus and transport service fee.',
                'is_compulsory' => false,
            ],
            [
                'name' => 'ICT Levy',
                'description' => 'Computer lab and digital learning resources.',
                'is_compulsory' => true,
            ],
            [
                'name' => 'Examination Fee',
                'description' => 'Term examination and assessment charges.',
                'is_compulsory' => true,
            ],
            [
                'name' => 'Library Fee',
                'description' => 'Library access and learning materials.',
                'is_compulsory' => false,
            ],
        ];

        return collect($items)->mapWithKeys(function (array $item) use ($adminId) {
            $record = BillingItem::firstOrCreate(
                ['name' => $item['name']],
                [
                    'description' => $item['description'],
                    'status' => 'Active',
                    'is_compulsory' => $item['is_compulsory'],
                    'created_by' => $adminId,
                ]
            );

            $record->update([
                'description' => $item['description'],
                'is_compulsory' => $item['is_compulsory'],
                'updated_by' => $adminId,
            ]);

            return [$item['name'] => $record];
        });
    }
}
