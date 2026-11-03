<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug', 50)->unique();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('assessment_types')->insert([
            [
                'name' => 'Homework',
                'slug' => 'homework',
                'sort_order' => 1,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Class Test',
                'slug' => 'class_test',
                'sort_order' => 2,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Exam',
                'slug' => 'exam',
                'sort_order' => 3,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Class Assignment',
                'slug' => 'class_assignment',
                'sort_order' => 4,
                'status' => 'Active',
                'created_by' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_types');
    }
};
