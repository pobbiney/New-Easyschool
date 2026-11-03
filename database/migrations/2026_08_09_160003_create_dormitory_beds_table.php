<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitory_beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dormitory_id')->constrained('dormitories')->cascadeOnDelete();
            $table->string('bed_label');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->timestamps();

            $table->unique(['dormitory_id', 'bed_label']);
            $table->unique('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitory_beds');
    }
};
