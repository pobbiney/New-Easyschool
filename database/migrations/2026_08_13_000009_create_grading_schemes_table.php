<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grading_schemes', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->string('letter_grade', 5);
            $table->string('remark')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        $defaults = [
            ['min' => 80, 'max' => 100, 'grade' => 'A', 'remark' => 'Excellent'],
            ['min' => 70, 'max' => 79.99, 'grade' => 'B', 'remark' => 'Very Good'],
            ['min' => 60, 'max' => 69.99, 'grade' => 'C', 'remark' => 'Good'],
            ['min' => 50, 'max' => 59.99, 'grade' => 'D', 'remark' => 'Pass'],
            ['min' => 0, 'max' => 49.99, 'grade' => 'F', 'remark' => 'Fail'],
        ];

        foreach ($defaults as $row) {
            DB::table('grading_schemes')->insert([
                'min_percentage' => $row['min'],
                'max_percentage' => $row['max'],
                'letter_grade' => $row['grade'],
                'remark' => $row['remark'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('grading_schemes');
    }
};
