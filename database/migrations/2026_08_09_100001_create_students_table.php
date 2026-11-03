<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique();
            $table->string('academic_year');
            $table->string('class_name');
            $table->string('section')->nullable();
            $table->string('roll_number')->nullable();
            $table->string('firstname');
            $table->string('othername')->nullable();
            $table->string('surname');
            $table->string('category')->nullable();
            $table->string('gender');
            $table->string('dob');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('picture')->nullable();

            $table->string('father_name')->nullable();
            $table->string('father_phone')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('father_photo')->nullable();

            $table->string('mother_name')->nullable();
            $table->string('mother_phone')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('mother_photo')->nullable();

            $table->string('guardian_type')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->string('guardian_address')->nullable();
            $table->string('guardian_photo')->nullable();

            $table->string('blood_group')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();

            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('national_id_number')->nullable();

            $table->string('previous_school_name')->nullable();
            $table->string('previous_school_address')->nullable();
            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('hostel')->nullable();
            $table->string('room_no')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('Active');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
