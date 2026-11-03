<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseTeachingAssignment;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseTeacherDemoSeeder extends Seeder
{
    private const TEACHER_CATEGORY_ID = 2;

    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;
        $countryId = '1';

        $classes = collect([
            ['name' => 'Primary 2'],
            ['name' => 'Primary 3'],
            ['name' => 'JHS 1'],
            ['name' => 'JHS 2'],
        ])->mapWithKeys(function (array $class) use ($adminId) {
            $record = SchoolClass::firstOrCreate(
                ['name' => $class['name']],
                [
                    'status' => 'Active',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            return [$class['name'] => $record];
        });

        $primaryOne = SchoolClass::where('name', 'Primary 1')->first();
        if ($primaryOne) {
            $classes->put('Primary 1', $primaryOne);
        }

        $teachers = $this->seedTeachers($adminId, $countryId);

        $existingTeacher = Staff::query()
            ->where('status', 'Active')
            ->whereHas('user', function ($query) {
                $query->where('user_cat', self::TEACHER_CATEGORY_ID)
                    ->where('status', 'Active');
            })
            ->first();

        if ($existingTeacher) {
            $teachers->push($existingTeacher);
        }

        $teachers = $teachers->unique('id')->values();

        $assignableCourses = Course::query()
            ->topLevel()
            ->with('subCourses')
            ->orderBy('name')
            ->get()
            ->flatMap(function (Course $course) {
                if ($course->subCourses->isNotEmpty()) {
                    return $course->subCourses;
                }

                return collect([$course]);
            });

        $assignmentPlan = [
            ['course' => 'Mathematics', 'class' => 'Primary 1', 'teachers' => 2],
            ['course' => 'Mathematics', 'class' => 'Primary 2', 'teachers' => 1],
            ['course' => 'Mathematics', 'class' => 'JHS 1', 'teachers' => 2],
            ['course' => 'English', 'class' => 'Primary 1', 'teachers' => 1],
            ['course' => 'Comprehension', 'class' => 'Primary 1', 'teachers' => 2],
            ['course' => 'Comprehension', 'class' => 'Primary 2', 'teachers' => 1],
            ['course' => 'Science', 'class' => 'Primary 3', 'teachers' => 2],
            ['course' => 'Science', 'class' => 'JHS 1', 'teachers' => 1],
            ['course' => 'Social Studies', 'class' => 'JHS 1', 'teachers' => 2],
            ['course' => 'Social Studies', 'class' => 'JHS 2', 'teachers' => 1],
            ['course' => 'Computing', 'class' => 'Primary 2', 'teachers' => 1],
            ['course' => 'Computing', 'class' => 'JHS 2', 'teachers' => 2],
            ['course' => 'Test A', 'class' => 'Primary 1', 'teachers' => 2],
        ];

        foreach ($assignmentPlan as $plan) {
            $course = $assignableCourses->firstWhere('name', $plan['course']);
            $schoolClass = $classes->get($plan['class']);

            if (! $course || ! $schoolClass || $teachers->isEmpty()) {
                continue;
            }

            $selectedTeachers = $teachers->take($plan['teachers']);

            foreach ($selectedTeachers as $index => $teacher) {
                CourseTeachingAssignment::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'school_class_id' => $schoolClass->id,
                        'staff_id' => $teacher->id,
                    ],
                    [
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ]
                );
            }
        }

        if ($classes->has('Primary 2') && $teachers->count() >= 1) {
            $classes['Primary 2']->update([
                'class_teacher_id' => $teachers[0]->id,
                'updated_by' => $adminId,
            ]);
        }

        if ($classes->has('JHS 1') && $teachers->count() >= 2) {
            $classes['JHS 1']->update([
                'class_teacher_id' => $teachers[1]->id,
                'updated_by' => $adminId,
            ]);
        }
    }

    private function seedTeachers(int $adminId, string $countryId)
    {
        $dummyTeachers = [
            [
                'title' => 'Mr',
                'firstname' => 'Kwame',
                'surname' => 'Mensah',
                'othername' => 'Kofi',
                'gender' => 'Male',
                'email' => 'kwame.mensah.demo@easyschool.test',
                'employee_id' => 'DEMO-TCH-001',
                'position' => 'Mathematics Teacher',
                'mobile' => '0244000001',
            ],
            [
                'title' => 'Mrs',
                'firstname' => 'Ama',
                'surname' => 'Boateng',
                'othername' => 'Serwaa',
                'gender' => 'Female',
                'email' => 'ama.boateng.demo@easyschool.test',
                'employee_id' => 'DEMO-TCH-002',
                'position' => 'English Teacher',
                'mobile' => '0244000002',
            ],
            [
                'title' => 'Ms',
                'firstname' => 'Efua',
                'surname' => 'Asante',
                'othername' => null,
                'gender' => 'Female',
                'email' => 'efua.asante.demo@easyschool.test',
                'employee_id' => 'DEMO-TCH-003',
                'position' => 'Science Teacher',
                'mobile' => '0244000003',
            ],
            [
                'title' => 'Mr',
                'firstname' => 'Yaw',
                'surname' => 'Owusu',
                'othername' => 'Nana',
                'gender' => 'Male',
                'email' => 'yaw.owusu.demo@easyschool.test',
                'employee_id' => 'DEMO-TCH-004',
                'position' => 'ICT Teacher',
                'mobile' => '0244000004',
            ],
        ];

        return collect($dummyTeachers)->map(function (array $teacher) use ($adminId, $countryId) {
            $staff = Staff::updateOrCreate(
                ['employee_id' => $teacher['employee_id']],
                [
                    'title' => $teacher['title'],
                    'firstname' => $teacher['firstname'],
                    'surname' => $teacher['surname'],
                    'othername' => $teacher['othername'],
                    'gender' => $teacher['gender'],
                    'email' => $teacher['email'],
                    'dob' => '1990-01-15',
                    'nationality' => $countryId,
                    'marital_status' => 'Single',
                    'position' => $teacher['position'],
                    'mobile' => $teacher['mobile'],
                    'residential_address' => 'Demo Street, Accra',
                    'status' => 'Active',
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]
            );

            $fullName = trim(collect([
                $teacher['title'],
                $teacher['firstname'],
                $teacher['othername'],
                $teacher['surname'],
            ])->filter()->implode(' '));

            User::updateOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $fullName,
                    'password' => Hash::make('password'),
                    'phone' => $teacher['mobile'],
                    'cat_id' => self::TEACHER_CATEGORY_ID,
                    'user_cat' => self::TEACHER_CATEGORY_ID,
                    'status' => 'Active',
                    'staff_id' => $staff->id,
                ]
            );

            return $staff;
        });
    }
}
