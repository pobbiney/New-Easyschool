<?php

namespace App\Support;

class IdCardEmployeeFields
{
    /**
     * @return array<string, array<string, string>>
     */
    public static function groups(): array
    {
        return [
            'Identity' => [
                'picture' => 'Photo',
                'employee_id' => 'Employee ID',
                'full_name' => 'Full name',
                'title' => 'Title',
                'firstname' => 'First name',
                'othername' => 'Other name',
                'surname' => 'Surname',
                'gender' => 'Gender',
                'dob' => 'Date of birth',
                'position' => 'Position',
                'status' => 'Status',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        $keys = [];

        foreach (self::groups() as $fields) {
            foreach (array_keys($fields) as $key) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    public static function defaults(): array
    {
        return ['picture', 'employee_id', 'full_name', 'gender', 'position'];
    }

    /**
     * @param  mixed  $stored
     * @return list<string>
     */
    public static function selected($stored): array
    {
        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (is_array($stored) && $stored !== []) {
            return array_values(array_intersect(self::keys(), array_map('strval', $stored)));
        }

        return self::defaults();
    }
}
