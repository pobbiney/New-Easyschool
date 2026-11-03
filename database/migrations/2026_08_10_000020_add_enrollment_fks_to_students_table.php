<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('school_class_id')->nullable()->after('class_name')->constrained('school_classes')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->after('academic_year')->constrained('academic_years')->nullOnDelete();
            $table->foreignId('academic_term_id')->nullable()->after('academic_year_id')->constrained('academic_terms')->nullOnDelete();
        });

        $classes = DB::table('school_classes')->pluck('id', 'name');
        $years = DB::table('academic_years')->pluck('id', 'name');
        $defaultTermId = DB::table('academic_terms')->where('sort_order', 1)->value('id');

        DB::table('students')->orderBy('id')->chunkById(100, function ($students) use ($classes, $years, $defaultTermId) {
            foreach ($students as $student) {
                DB::table('students')->where('id', $student->id)->update([
                    'school_class_id' => $classes[$student->class_name] ?? null,
                    'academic_year_id' => $years[$student->academic_year] ?? null,
                    'academic_term_id' => $defaultTermId,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('school_class_id');
            $table->dropConstrainedForeignId('academic_year_id');
            $table->dropConstrainedForeignId('academic_term_id');
        });
    }
};
