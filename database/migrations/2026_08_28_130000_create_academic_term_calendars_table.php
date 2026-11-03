<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academic_term_calendars')) {
            return;
        }

        Schema::create('academic_term_calendars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
            $table->date('opening_date');
            $table->date('vacation_date');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->unique(
                ['school_id', 'academic_year_id', 'academic_term_id'],
                'term_calendars_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_term_calendars');
    }
};
