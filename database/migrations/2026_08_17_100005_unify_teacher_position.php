<?php

use App\Support\TeacherCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_positions')) {
            return;
        }

        $now = now();
        $teachingId = DB::table('hr_departments')->where('code', 'TEACH')->value('id')
            ?? DB::table('hr_departments')->where('name', 'Teaching')->value('id');

        if (! $teachingId && Schema::hasTable('hr_departments')) {
            $teachingId = DB::table('hr_departments')->insertGetId([
                'name' => 'Teaching',
                'code' => 'TEACH',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $teacherId = DB::table('hr_positions')->where('name', 'Teacher')->value('id');
        if (! $teacherId) {
            $teacherId = DB::table('hr_positions')->insertGetId([
                'department_id' => $teachingId,
                'name' => 'Teacher',
                'status' => 'Active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('hr_positions')->where('id', $teacherId)->update([
                'status' => 'Active',
                'department_id' => $teachingId ?: DB::table('hr_positions')->where('id', $teacherId)->value('department_id'),
                'updated_at' => $now,
            ]);
        }

        $keepNames = ['Teacher', 'Head Teacher', 'Deputy Head Teacher'];
        $legacyPositionIds = DB::table('hr_positions')
            ->whereNotIn('name', $keepNames)
            ->where(function ($query) {
                $query->where('name', 'like', '% Teacher')
                    ->orWhere('name', 'like', '%Teacher');
            })
            ->pluck('id')
            ->all();

        $teacherStaffIds = DB::table('users')
            ->where('user_cat', TeacherCategory::id())
            ->whereNotNull('staff_id')
            ->pluck('staff_id')
            ->all();

        $staffQuery = DB::table('staff')->where(function ($query) use ($legacyPositionIds, $teacherStaffIds) {
            if ($legacyPositionIds) {
                $query->whereIn('position_id', $legacyPositionIds);
            }
            if ($teacherStaffIds) {
                $query->orWhereIn('id', $teacherStaffIds);
            }
            $query->orWhereIn('position', [
                'Mathematics Teacher',
                'English Teacher',
                'Science Teacher',
                'Social Studies Teacher',
                'ICT Teacher',
                'French Teacher',
                'Teacher',
            ]);
        })
            ->whereNotIn('position', ['Head Teacher', 'Deputy Head Teacher']);

        $protectedPositionIds = DB::table('hr_positions')
            ->whereIn('name', ['Head Teacher', 'Deputy Head Teacher'])
            ->pluck('id')
            ->all();

        if ($protectedPositionIds) {
            $staffQuery->where(function ($query) use ($protectedPositionIds) {
                $query->whereNull('position_id')->orWhereNotIn('position_id', $protectedPositionIds);
            });
        }

        $staffIds = $staffQuery->pluck('id')->all();

        if ($staffIds) {
            $payload = [
                'position_id' => $teacherId,
                'position' => 'Teacher',
                'updated_at' => $now,
            ];
            if ($teachingId) {
                $payload['department_id'] = $teachingId;
            }
            DB::table('staff')->whereIn('id', $staffIds)->update($payload);
        }

        if ($legacyPositionIds) {
            $stillUsed = DB::table('staff')->whereIn('position_id', $legacyPositionIds)->pluck('position_id')->unique()->all();
            $unused = array_diff($legacyPositionIds, $stillUsed);
            if ($unused) {
                DB::table('hr_positions')->whereIn('id', $unused)->delete();
            }
        }
    }

    public function down(): void
    {
        //
    }
};
