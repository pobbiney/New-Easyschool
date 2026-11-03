<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('house_id')->nullable()->after('notes')->constrained('houses')->nullOnDelete();
            $table->foreignId('dormitory_id')->nullable()->after('house_id')->constrained('dormitories')->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->after('dormitory_id')->constrained('dormitory_beds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['house_id']);
            $table->dropForeign(['dormitory_id']);
            $table->dropForeign(['bed_id']);
            $table->dropColumn(['house_id', 'dormitory_id', 'bed_id']);
        });
    }
};
