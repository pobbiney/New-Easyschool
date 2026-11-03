<?php

use App\Models\SchoolSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('school_class_id');
            $table->unsignedBigInteger('academic_term_id')->nullable()->after('academic_year_id');

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->nullOnDelete();
        });

        $school = SchoolSetting::current();
        $yearId = $school->defaultAcademicYearId();
        $termId = $school->defaultAcademicTermId();

        if ($yearId || $termId) {
            DB::table('course_teaching_assignments')->update([
                'academic_year_id' => $yearId,
                'academic_term_id' => $termId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('course_teaching_assignments', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['academic_term_id']);
            $table->dropColumn(['academic_year_id', 'academic_term_id']);
        });
    }
};
