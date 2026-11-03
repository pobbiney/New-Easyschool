<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->foreignId('default_academic_year_id')
                ->nullable()
                ->after('logo_path')
                ->constrained('academic_years')
                ->nullOnDelete();

            $table->foreignId('default_academic_term_id')
                ->nullable()
                ->after('default_academic_year_id')
                ->constrained('academic_terms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropForeign(['default_academic_year_id']);
            $table->dropForeign(['default_academic_term_id']);
            $table->dropColumn(['default_academic_year_id', 'default_academic_term_id']);
        });
    }
};
