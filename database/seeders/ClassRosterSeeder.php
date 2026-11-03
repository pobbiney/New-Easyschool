<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClassRosterSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;

        $academicYear = AcademicYear::query()
            ->where('status', 'Active')
            ->orderByDesc('id')
            ->first();

        if (! $academicYear) {
            $this->command?->warn('No active academic year found.');

            return;
        }

        $academicTerm = AcademicTerm::query()
            ->where('status', 'Active')
            ->orderBy('sort_order')
            ->first();

        if (! $academicTerm) {
            $this->command?->warn('No active academic term found.');

            return;
        }

        $classes = SchoolClass::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        if ($classes->isEmpty()) {
            $this->command?->warn('No school classes found.');

            return;
        }

        $maleFirst = ['Kofi', 'Kwame', 'Yaw', 'Kojo', 'Nana', 'Kwesi', 'Papa', 'Kobina', 'Fiifi', 'Ekow'];
        $femaleFirst = ['Akosua', 'Ama', 'Abena', 'Adwoa', 'Efua', 'Akua', 'Serwaa', 'Yaa', 'Afia', 'Araba'];
        $surnames = ['Mensah', 'Asante', 'Boateng', 'Owusu', 'Adjei', 'Darko', 'Osei', 'Appiah', 'Amoah', 'Tetteh', 'Sarpong', 'Nyarko', 'Quaye'];

        $prefix = strtoupper(trim(config('school.student_id_prefix', 'STD')));
        $padLength = max(1, (int) config('school.student_id_pad_length', 3));
        $pattern = '/^'.preg_quote($prefix, '/').'-(\d+)$/i';

        $nextNumber = (Student::pluck('student_id')
            ->map(function ($studentId) use ($pattern) {
                if (preg_match($pattern, $studentId, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max() ?? 0) + 1;

        $created = 0;

        foreach ($classes as $class) {
            for ($i = 1; $i <= 10; $i++) {
                $isMale = $i % 2 === 1;
                $firstPool = $isMale ? $maleFirst : $femaleFirst;
                $firstname = $firstPool[($i - 1) % count($firstPool)];
                $surname = $surnames[($class->id + $i - 1) % count($surnames)];
                $studentId = $prefix.'-'.str_pad((string) $nextNumber, $padLength, '0', STR_PAD_LEFT);
                $nextNumber++;

                $yearBorn = match (true) {
                    str_contains(strtolower($class->name), 'nursery') => 2021,
                    str_contains(strtolower($class->name), 'kindergarten') => 2020,
                    str_contains(strtolower($class->name), 'class 1') || $class->name === 'Class 1' => 2019,
                    str_contains(strtolower($class->name), 'class 2') || $class->name === 'Class 2' => 2018,
                    str_contains(strtolower($class->name), 'class 3') || $class->name === 'Class 3' => 2017,
                    str_contains(strtolower($class->name), 'class 4') => 2016,
                    str_contains(strtolower($class->name), 'class 5') => 2015,
                    str_contains(strtolower($class->name), 'class 6') => 2014,
                    str_contains(strtolower($class->name), 'jhs 1') => 2013,
                    str_contains(strtolower($class->name), 'jhs 2') => 2012,
                    str_contains(strtolower($class->name), 'jhs 3') => 2011,
                    default => 2015,
                };

                $month = str_pad((string) (($i % 12) + 1), 2, '0', STR_PAD_LEFT);
                $day = str_pad((string) ((($class->id * 3 + $i) % 28) + 1), 2, '0', STR_PAD_LEFT);

                Student::updateOrCreate(
                    ['student_id' => $studentId],
                    [
                        'academic_year' => $academicYear->name,
                        'academic_year_id' => $academicYear->id,
                        'academic_term_id' => $academicTerm->id,
                        'school_class_id' => $class->id,
                        'class_name' => $class->name,
                        'section' => 'A',
                        'roll_number' => str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                        'firstname' => $firstname,
                        'othername' => $isMale ? 'Nana' : 'Ama',
                        'surname' => $surname,
                        'gender' => $isMale ? 'Male' : 'Female',
                        'dob' => "{$yearBorn}-{$month}-{$day}",
                        'phone' => '0244'.str_pad((string) (100000 + ($class->id * 10) + $i), 6, '0', STR_PAD_LEFT),
                        'email' => strtolower($firstname.'.'.$surname.$i.'@student.test'),
                        'father_name' => 'Mr. '.$surname,
                        'father_phone' => '0245'.str_pad((string) (200000 + ($class->id * 10) + $i), 6, '0', STR_PAD_LEFT),
                        'father_occupation' => 'Trader',
                        'mother_name' => 'Mrs. '.$surname,
                        'mother_phone' => '0246'.str_pad((string) (300000 + ($class->id * 10) + $i), 6, '0', STR_PAD_LEFT),
                        'mother_occupation' => 'Nurse',
                        'guardian_type' => $isMale ? 'Father' : 'Mother',
                        'guardian_name' => ($isMale ? 'Mr. ' : 'Mrs. ').$surname,
                        'guardian_phone' => '0245'.str_pad((string) (200000 + ($class->id * 10) + $i), 6, '0', STR_PAD_LEFT),
                        'current_address' => 'Accra, Ghana',
                        'status' => 'Active',
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ]
                );

                $created++;
            }
        }

        $this->command?->info("Seeded {$created} students across {$classes->count()} classes.");
    }
}
