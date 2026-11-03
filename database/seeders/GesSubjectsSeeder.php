<?php

namespace Database\Seeders;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;

class GesSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;

        $levels = [
            'Primary School (GES)' => [
                'description' => 'NaCCA standard-based curriculum subjects for Kindergarten to Primary 6 (Basic 1–6).',
                'subjects' => [
                    'English Language',
                    'Mathematics',
                    'Science',
                    'History',
                    'Our World Our People',
                    'Creative Arts and Design',
                    'Religious and Moral Education',
                    'Physical Education',
                    'Ghanaian Language',
                    'French',
                    'Computing',
                ],
            ],
            'Junior High School (GES)' => [
                'description' => 'Common Core Programme (CCP) learning areas for JHS 1–3 (Basic 7–9).',
                'subjects' => [
                    'English Language',
                    'Mathematics',
                    'Science',
                    'Social Studies',
                    'Creative Arts and Design',
                    'Computing',
                    'Career Technology',
                    'Religious and Moral Education',
                    'Ghanaian Language',
                    'French',
                    'Physical and Health Education',
                    'Arabic',
                ],
            ],
        ];

        $createdParents = 0;
        $createdSubjects = 0;

        foreach ($levels as $levelName => $meta) {
            $parent = Course::updateOrCreate(
                ['name' => $levelName, 'parent_id' => null],
                [
                    'category' => 'Subject',
                    'description' => $meta['description'],
                    'status' => 'Active',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            $createdParents++;

            foreach ($meta['subjects'] as $subjectName) {
                Course::updateOrCreate(
                    ['name' => $subjectName, 'parent_id' => $parent->id],
                    [
                        'category' => 'Subject',
                        'description' => "GES-approved {$subjectName} for {$levelName}.",
                        'status' => 'Active',
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ]
                );

                $createdSubjects++;
            }
        }

        $this->syncClassCategoryLinks();

        $this->command?->info("Seeded {$createdParents} level groups with {$createdSubjects} GES subjects.");
    }

    private function syncClassCategoryLinks(): void
    {
        $primaryParent = Course::query()->where('name', 'Primary School (GES)')->whereNull('parent_id')->first();
        $jhsParent = Course::query()->where('name', 'Junior High School (GES)')->whereNull('parent_id')->first();

        $primaryCategories = ClassCategory::query()
            ->whereIn('name', ['Pre School', 'Lower Primary', 'Upper Primary'])
            ->pluck('id');

        $jhsCategories = ClassCategory::query()
            ->where('name', 'Junior High')
            ->pluck('id');

        if ($primaryParent) {
            $courseIds = Course::query()->where('parent_id', $primaryParent->id)->pluck('id');
            foreach ($primaryCategories as $categoryId) {
                ClassCategory::find($categoryId)?->courses()->syncWithoutDetaching($courseIds);
            }
        }

        if ($jhsParent) {
            $courseIds = Course::query()->where('parent_id', $jhsParent->id)->pluck('id');
            foreach ($jhsCategories as $categoryId) {
                ClassCategory::find($categoryId)?->courses()->syncWithoutDetaching($courseIds);
            }
        }
    }
}
