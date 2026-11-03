<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Seeder;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::query()->value('id') ?? 1;

        $years = [
            '2023/2024',
            '2024/2025',
            '2025/2026',
            '2026/2027',
            '2027/2028',
        ];

        foreach ($years as $year) {
            AcademicYear::firstOrCreate(
                ['name' => $year],
                [
                    'status' => 'Active',
                    'created_by' => $userId,
                ]
            );
        }
    }
}
