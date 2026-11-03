<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE t1 FROM course_teaching_assignments t1
            INNER JOIN course_teaching_assignments t2
                ON t1.course_id = t2.course_id
                AND t1.school_class_id = t2.school_class_id
                AND t1.id > t2.id
        ');

        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('course_class_staff_unique');
            $table->unique(['course_id', 'school_class_id'], 'course_class_unique');
        });
    }

    public function down(): void
    {
        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->dropUnique('course_class_unique');
            $table->unique(['course_id', 'school_class_id', 'staff_id'], 'course_class_staff_unique');
        });
    }
};
