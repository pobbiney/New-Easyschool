<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('surname');
            $table->string('othername')->nullable();
            $table->string('firstname');
            $table->string('gender');
            $table->string('dob');
            $table->string('nationality');
            $table->string('employee_id');
            $table->string('marital_status');
            $table->string('position');
            $table->string('picture')->nullable();
            $table->string('mobile');

            $table->string('residential_address');
            $table->string('status');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
