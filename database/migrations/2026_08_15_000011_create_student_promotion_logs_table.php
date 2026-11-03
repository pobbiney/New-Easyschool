<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_promotion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('from_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('to_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->string('promotion_type', 20);
            $table->unsignedSmallInteger('aggregate_total_score')->nullable();
            $table->unsignedSmallInteger('promotion_minimum_mark')->nullable();
            $table->unsignedBigInteger('promoted_by');
            $table->timestamps();

            $table->unique(
                ['student_id', 'from_class_id', 'academic_year_id', 'academic_term_id'],
                'student_promotion_logs_unique_per_term'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_promotion_logs');
    }
};
