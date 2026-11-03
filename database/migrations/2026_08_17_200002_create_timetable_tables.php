<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sort_order')->default(1);
            $table->string('label');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('kind', 20)->default('lesson');
            $table->timestamps();
        });

        Schema::create('class_timetables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_class_id');
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->unsignedBigInteger('academic_term_id')->nullable();
            $table->string('status', 20)->default('generated');
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'academic_year_id', 'academic_term_id'], 'class_timetables_period_unique');
        });

        Schema::create('class_timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_timetable_id');
            $table->unsignedTinyInteger('day');
            $table->unsignedBigInteger('timetable_period_id');
            $table->unsignedBigInteger('course_id')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->timestamps();

            $table->unique(['class_timetable_id', 'day', 'timetable_period_id'], 'class_timetable_slot_unique');
        });

        $bell = [
            ['sort_order' => 1, 'label' => 'Assembly & Registration', 'start_time' => '07:30:00', 'end_time' => '08:00:00', 'kind' => 'assembly'],
            ['sort_order' => 2, 'label' => 'Period 1', 'start_time' => '08:00:00', 'end_time' => '08:50:00', 'kind' => 'lesson'],
            ['sort_order' => 3, 'label' => 'Period 2', 'start_time' => '08:50:00', 'end_time' => '09:40:00', 'kind' => 'lesson'],
            ['sort_order' => 4, 'label' => 'Period 3', 'start_time' => '09:40:00', 'end_time' => '10:30:00', 'kind' => 'lesson'],
            ['sort_order' => 5, 'label' => 'Break', 'start_time' => '10:30:00', 'end_time' => '11:00:00', 'kind' => 'break'],
            ['sort_order' => 6, 'label' => 'Period 4', 'start_time' => '11:00:00', 'end_time' => '11:50:00', 'kind' => 'lesson'],
            ['sort_order' => 7, 'label' => 'Period 5', 'start_time' => '11:50:00', 'end_time' => '12:40:00', 'kind' => 'lesson'],
            ['sort_order' => 8, 'label' => 'Period 6', 'start_time' => '12:40:00', 'end_time' => '13:30:00', 'kind' => 'lesson'],
            ['sort_order' => 9, 'label' => 'Break', 'start_time' => '13:30:00', 'end_time' => '14:00:00', 'kind' => 'break'],
            ['sort_order' => 10, 'label' => 'Period 7', 'start_time' => '14:00:00', 'end_time' => '14:50:00', 'kind' => 'lesson'],
            ['sort_order' => 11, 'label' => 'Period 8', 'start_time' => '14:50:00', 'end_time' => '15:40:00', 'kind' => 'lesson'],
        ];

        $now = now();
        foreach ($bell as $row) {
            DB::table('timetable_periods')->insert($row + [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_timetable_entries');
        Schema::dropIfExists('class_timetables');
        Schema::dropIfExists('timetable_periods');
    }
};
