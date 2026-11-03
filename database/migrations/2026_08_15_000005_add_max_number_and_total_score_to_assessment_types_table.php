<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_number')->default(1)->after('sort_order');
            $table->decimal('total_score', 8, 2)->default(100)->after('max_number');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_types', function (Blueprint $table) {
            $table->dropColumn(['max_number', 'total_score']);
        });
    }
};
