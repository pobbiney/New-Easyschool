<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dormitories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('bed_count')->default(1);
            $table->string('status')->default('Active');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['house_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dormitories');
    }
};
