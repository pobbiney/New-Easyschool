<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('super_admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('status', 20)->default('Active');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->nullable()->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('status', 20)->default('pending');
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('admin_phone')->nullable();
            $table->string('admin_password');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('super_admins')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('school_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('school_code', 32)->nullable();
            $table->string('actor_type', 50)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->json('payload')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['school_id', 'created_at']);
            $table->index(['school_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_activity_logs');
        Schema::dropIfExists('schools');
        Schema::dropIfExists('super_admins');
    }
};
