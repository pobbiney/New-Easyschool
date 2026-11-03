<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;

        $academicYear = AcademicYear::query()
            ->where('status', 'Active')
            ->orderByDesc('id')
            ->first();

        if (! $academicYear) {
            $this->command?->warn('No active academic year found. Run AcademicYearSeeder first.');

            return;
        }

        $academicTerm = AcademicTerm::query()->orderBy('id')->first();

        if (! $academicTerm) {
            $this->command?->warn('No academic term found.');

            return;
        }

        $classes = SchoolClass::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get()
            ->keyBy('name');

        if ($classes->isEmpty()) {
            $this->command?->warn('No school classes found. Run CourseTeacherDemoSeeder first.');

            return;
        }

        $dummyStudents = [
            [
                'student_id' => 'STD-00002',
                'class' => 'Primary 1',
                'firstname' => 'Akosua',
                'othername' => 'Ama',
                'surname' => 'Mensah',
                'gender' => 'Female',
                'dob' => '2018-03-12',
                'phone' => '0244111001',
                'roll_number' => '001',
            ],
            [
                'student_id' => 'STD-00003',
                'class' => 'Primary 1',
                'firstname' => 'Kofi',
                'othername' => null,
                'surname' => 'Asante',
                'gender' => 'Male',
                'dob' => '2018-07-25',
                'phone' => '0244111002',
                'roll_number' => '002',
            ],
            [
                'student_id' => 'STD-00004',
                'class' => 'Primary 2',
                'firstname' => 'Abena',
                'othername' => 'Serwaa',
                'surname' => 'Boateng',
                'gender' => 'Female',
                'dob' => '2017-01-08',
                'phone' => '0244111003',
                'roll_number' => '001',
            ],
            [
                'student_id' => 'STD-00005',
                'class' => 'Primary 2',
                'firstname' => 'Yaw',
                'othername' => 'Nana',
                'surname' => 'Owusu',
                'gender' => 'Male',
                'dob' => '2017-09-14',
                'phone' => '0244111004',
                'roll_number' => '002',
            ],
            [
                'student_id' => 'STD-00006',
                'class' => 'Primary 3',
                'firstname' => 'Efua',
                'othername' => null,
                'surname' => 'Adjei',
                'gender' => 'Female',
                'dob' => '2016-05-20',
                'phone' => '0244111005',
                'roll_number' => '001',
            ],
            [
                'student_id' => 'STD-00007',
                'class' => 'Primary 3',
                'firstname' => 'Kwame',
                'othername' => 'Kojo',
                'surname' => 'Darko',
                'gender' => 'Male',
                'dob' => '2016-11-03',
                'phone' => '0244111006',
                'roll_number' => '002',
            ],
            [
                'student_id' => 'STD-00008',
                'class' => 'JHS 1',
                'firstname' => 'Ama',
                'othername' => 'Akua',
                'surname' => 'Osei',
                'gender' => 'Female',
                'dob' => '2013-04-17',
                'phone' => '0244111007',
                'roll_number' => '001',
            ],
            [
                'student_id' => 'STD-00009',
                'class' => 'JHS 1',
                'firstname' => 'Nana',
                'othername' => 'Kwesi',
                'surname' => 'Appiah',
                'gender' => 'Male',
                'dob' => '2013-08-29',
                'phone' => '0244111008',
                'roll_number' => '002',
            ],
            [
                'student_id' => 'STD-00010',
                'class' => 'JHS 2',
                'firstname' => 'Adwoa',
                'othername' => null,
                'surname' => 'Amoah',
                'gender' => 'Female',
                'dob' => '2012-02-11',
                'phone' => '0244111009',
                'roll_number' => '001',
            ],
            [
                'student_id' => 'STD-00011',
                'class' => 'JHS 2',
                'firstname' => 'Kojo',
                'othername' => 'Papa',
                'surname' => 'Tetteh',
                'gender' => 'Male',
                'dob' => '2012-12-06',
                'phone' => '0244111010',
                'roll_number' => '002',
            ],
        ];

        foreach ($dummyStudents as $data) {
            $schoolClass = $classes->get($data['class']);

            if (! $schoolClass) {
                continue;
            }

            Student::updateOrCreate(
                ['student_id' => $data['student_id']],
                [
                    'academic_year' => $academicYear->name,
                    'academic_year_id' => $academicYear->id,
                    'academic_term_id' => $academicTerm->id,
                    'school_class_id' => $schoolClass->id,
                    'class_name' => $schoolClass->name,
                    'section' => 'A',
                    'roll_number' => $data['roll_number'],
                    'firstname' => $data['firstname'],
                    'othername' => $data['othername'],
                    'surname' => $data['surname'],
                    'gender' => $data['gender'],
                    'dob' => $data['dob'],
                    'phone' => $data['phone'],
                    'father_name' => 'Mr. ' . $data['surname'],
                    'father_phone' => '0244222001',
                    'father_occupation' => 'Trader',
                    'mother_name' => 'Mrs. ' . $data['surname'],
                    'mother_phone' => '0244222002',
                    'mother_occupation' => 'Nurse',
                    'guardian_type' => 'Father',
                    'guardian_name' => 'Mr. ' . $data['surname'],
                    'guardian_phone' => '0244222001',
                    'current_address' => 'East Legon, Accra',
                    'status' => 'Active',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );
        }
    }
}
