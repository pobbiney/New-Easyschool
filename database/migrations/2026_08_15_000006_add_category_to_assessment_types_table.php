<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_types', function (Blueprint $table) {
            $table->string('category', 40)->default('class_assessment')->after('slug');
        });

        DB::table('assessment_types')
            ->where('slug', 'exam')
            ->update(['category' => 'examination_assessment']);

        DB::table('assessment_types')
            ->where('slug', '!=', 'exam')
            ->update(['category' => 'class_assessment']);
    }

    public function down(): void
    {
        Schema::table('assessment_types', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
