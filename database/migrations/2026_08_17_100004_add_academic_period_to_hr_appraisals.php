<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_appraisals', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_year_id')->nullable()->after('staff_id');
            $table->unsignedBigInteger('academic_term_id')->nullable()->after('academic_year_id');

            $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
            $table->foreign('academic_term_id')->references('id')->on('academic_terms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_appraisals', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['academic_term_id']);
            $table->dropColumn(['academic_year_id', 'academic_term_id']);
        });
    }
};
