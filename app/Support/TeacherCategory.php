<?php

namespace App\Support;

use App\Models\UserCat;

class TeacherCategory
{
    public static function id(): int
    {
        $id = UserCat::query()
            ->where('cat_name', 'Teacher')
            ->value('cat_id');

        return $id ? (int) $id : 2;
    }
}
