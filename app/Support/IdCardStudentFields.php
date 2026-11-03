<?php

namespace App\Support;

class IdCardStudentFields
{
    /**
     * Printable student fields, grouped like the student record.
     *
     * @return array<string, array<string, string>>
     */
    public static function groups(): array
    {
        return [
            'Identity' => [
                'picture' => 'Photo',
                'student_id' => 'Student ID',
                'full_name' => 'Full name',
                'firstname' => 'First name',
                'othername' => 'Other name',
                'surname' => 'Surname',
                'gender' => 'Gender',
                'dob' => 'Date of birth',
                'category' => 'Category',
                'status' => 'Status',
                'nhis_number' => 'NHIA number',
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
        return ['picture', 'student_id', 'full_name', 'gender', 'dob'];
    }

    /**
     * @param  mixed  $stored
     * @param  object|null  $legacy
     * @return list<string>
     */
    public static function selected($stored, ?object $legacy = null): array
    {
        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            $stored = is_array($decoded) ? $decoded : [];
        }

        if (is_array($stored) && $stored !== []) {
            return array_values(array_intersect(self::keys(), array_map('strval', $stored)));
        }

        $fallback = ['full_name'];

        if ($legacy) {
            if (! empty($legacy->show_photo)) {
                $fallback[] = 'picture';
            }
            if (! empty($legacy->show_id_number)) {
                $fallback[] = 'student_id';
            }
        } else {
            $fallback = self::defaults();
        }

        return array_values(array_unique($fallback));
    }
}
