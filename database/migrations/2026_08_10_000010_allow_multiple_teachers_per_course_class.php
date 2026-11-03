<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->index('course_id', 'cta_course_id_index');
            $table->index('school_class_id', 'cta_school_class_id_index');
            $table->unique(['course_id', 'school_class_id', 'staff_id'], 'course_class_staff_unique');
            $table->dropUnique(['course_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->unique(['course_id', 'school_class_id']);
            $table->dropUnique('course_class_staff_unique');
            $table->dropIndex('cta_course_id_index');
            $table->dropIndex('cta_school_class_id_index');
        });
    }
};
