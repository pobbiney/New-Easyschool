<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_category_course', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_category_id')->constrained('class_categories')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_category_id', 'course_id']);
        });

        $primaryParent = DB::table('courses')->where('name', 'Primary School (GES)')->whereNull('parent_id')->first();
        $jhsParent = DB::table('courses')->where('name', 'Junior High School (GES)')->whereNull('parent_id')->first();

        if (! $primaryParent && ! $jhsParent) {
            return;
        }

        $primaryCategoryIds = DB::table('class_categories')
            ->whereIn('name', ['Pre School', 'Lower Primary', 'Upper Primary'])
            ->pluck('id');

        $jhsCategoryIds = DB::table('class_categories')
            ->where('name', 'Junior High')
            ->pluck('id');

        $now = now();

        if ($primaryParent) {
            $primaryCourseIds = DB::table('courses')
                ->where('parent_id', $primaryParent->id)
                ->pluck('id');

            foreach ($primaryCategoryIds as $categoryId) {
                foreach ($primaryCourseIds as $courseId) {
                    DB::table('class_category_course')->insert([
                        'class_category_id' => $categoryId,
                        'course_id' => $courseId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        if ($jhsParent) {
            $jhsCourseIds = DB::table('courses')
                ->where('parent_id', $jhsParent->id)
                ->pluck('id');

            foreach ($jhsCategoryIds as $categoryId) {
                foreach ($jhsCourseIds as $courseId) {
                    DB::table('class_category_course')->insert([
                        'class_category_id' => $categoryId,
                        'course_id' => $courseId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_category_course');
    }
};
