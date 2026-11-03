<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->unsignedBigInteger('class_teacher_id')->nullable()->after('status');
            $table->foreign('class_teacher_id')->references('id')->on('staff')->nullOnDelete();
            $table->unique('class_teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['class_teacher_id']);
            $table->dropUnique(['class_teacher_id']);
            $table->dropColumn('class_teacher_id');
        });
    }
};
