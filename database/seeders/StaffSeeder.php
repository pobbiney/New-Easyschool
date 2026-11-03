<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->value('id') ?? 1;

        $nationality = Country::query()->firstOrCreate(
            ['name' => 'Ghana'],
        )->id;

        $staffMembers = [
            ['title' => 'Mr', 'firstname' => 'Kwame', 'othername' => 'Kofi', 'surname' => 'Mensah', 'gender' => 'Male', 'position' => 'Head Teacher', 'email' => 'kwame.mensah@easyschool.test', 'phone' => '0244123456', 'dob' => '1980-03-15', 'marital_status' => 'Married'],
            ['title' => 'Mrs', 'firstname' => 'Ama', 'othername' => 'Serwaa', 'surname' => 'Asante', 'gender' => 'Female', 'position' => 'Deputy Head Teacher', 'email' => 'ama.asante@easyschool.test', 'phone' => '0244234567', 'dob' => '1982-07-22', 'marital_status' => 'Married'],
            ['title' => 'Mr', 'firstname' => 'Yaw', 'othername' => null, 'surname' => 'Boateng', 'gender' => 'Male', 'position' => 'Teacher', 'email' => 'yaw.boateng@easyschool.test', 'phone' => '0244345678', 'dob' => '1988-11-08', 'marital_status' => 'Single'],
            ['title' => 'Miss', 'firstname' => 'Abena', 'othername' => 'Akua', 'surname' => 'Owusu', 'gender' => 'Female', 'position' => 'Teacher', 'email' => 'abena.owusu@easyschool.test', 'phone' => '0244456789', 'dob' => '1990-01-30', 'marital_status' => 'Single'],
            ['title' => 'Mr', 'firstname' => 'Kojo', 'othername' => null, 'surname' => 'Adjei', 'gender' => 'Male', 'position' => 'Teacher', 'email' => 'kojo.adjei@easyschool.test', 'phone' => '0244567890', 'dob' => '1987-05-12', 'marital_status' => 'Married'],
            ['title' => 'Mrs', 'firstname' => 'Efua', 'othername' => null, 'surname' => 'Darko', 'gender' => 'Female', 'position' => 'Teacher', 'email' => 'efua.darko@easyschool.test', 'phone' => '0244678901', 'dob' => '1989-09-18', 'marital_status' => 'Married'],
            ['title' => 'Mr', 'firstname' => 'Fiifi', 'othername' => 'Ekow', 'surname' => 'Osei', 'gender' => 'Male', 'position' => 'Teacher', 'email' => 'fiifi.osei@easyschool.test', 'phone' => '0244789012', 'dob' => '1991-04-25', 'marital_status' => 'Single'],
            ['title' => 'Miss', 'firstname' => 'Adwoa', 'othername' => null, 'surname' => 'Appiah', 'gender' => 'Female', 'position' => 'Teacher', 'email' => 'adwoa.appiah@easyschool.test', 'phone' => '0244890123', 'dob' => '1992-08-03', 'marital_status' => 'Single'],
            ['title' => 'Mr', 'firstname' => 'Nana', 'othername' => 'Kwesi', 'surname' => 'Amoah', 'gender' => 'Male', 'position' => 'Bursar', 'email' => 'nana.amoah@easyschool.test', 'phone' => '0244901234', 'dob' => '1979-12-10', 'marital_status' => 'Married'],
            ['title' => 'Mrs', 'firstname' => 'Akua', 'othername' => 'Yaa', 'surname' => 'Tetteh', 'gender' => 'Female', 'position' => 'Administrative Officer', 'email' => 'akua.tetteh@easyschool.test', 'phone' => '0244012345', 'dob' => '1985-06-14', 'marital_status' => 'Married'],
        ];

        $nextNumber = (Staff::query()
            ->pluck('employee_id')
            ->map(function ($employeeId) {
                if (preg_match('/-(\d+)$/', (string) $employeeId, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max() ?? 0) + 1;

        $addresses = [
            '12 Independence Avenue, Accra',
            '45 Ring Road Central, Accra',
            '8 Spintex Road, Accra',
            '22 Adenta Housing Down, Accra',
            '5 Osu Oxford Street, Accra',
            '14 Tema Community 4',
            '33 Madina Estate, Accra',
            '7 Kaneshie First Light, Accra',
            '19 Kasoa New Market Road',
            '3 East Legon, Accra',
        ];

        foreach ($staffMembers as $index => $member) {
            $employeeId = 'STAFF-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
            $nextNumber++;

            Staff::query()->create([
                'title' => $member['title'],
                'surname' => $member['surname'],
                'firstname' => $member['firstname'],
                'othername' => $member['othername'],
                'gender' => $member['gender'],
                'email' => $member['email'],
                'mobile' => $member['phone'],
                'dob' => $member['dob'],
                'nationality' => (string) $nationality,
                'employee_id' => $employeeId,
                'marital_status' => $member['marital_status'],
                'position' => $member['position'],
                'residential_address' => $addresses[$index],
                'status' => 'Active',
                'created_by' => $adminId,
            ]);
        }

        $this->command?->info('Created 10 staff records.');
    }
}
