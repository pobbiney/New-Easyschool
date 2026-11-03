<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_periods', function (Blueprint $table) {
            $table->unsignedBigInteger('course_id')->nullable()->after('kind');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('course_id');
        });

        $rows = DB::table('timetable_periods')->get();
        foreach ($rows as $row) {
            $start = strtotime((string) $row->start_time);
            $end = strtotime((string) $row->end_time);
            $minutes = ($start && $end && $end > $start) ? (int) round(($end - $start) / 60) : 50;

            DB::table('timetable_periods')->where('id', $row->id)->update([
                'duration_minutes' => $minutes,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('timetable_periods', function (Blueprint $table) {
            $table->dropColumn(['course_id', 'duration_minutes']);
        });
    }
};
