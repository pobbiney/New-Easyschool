<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('parent_accounts')) {
            return;
        }

        Schema::create('parent_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('guardian_name')->nullable();
            $table->string('password');
            $table->string('status')->default('Active');
            $table->boolean('must_change_password')->default(false);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });

        Schema::create('parent_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_account_id')->constrained('parent_accounts')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->text('message');
            $table->string('status')->default('new');
            $table->text('admin_reply')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('parent_communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_account_id')->nullable()->constrained('parent_accounts')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->string('channel');
            $table->text('message');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['parent_account_id', 'sent_at']);
        });

        Schema::table('bill_payment_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('bill_payment_transactions', 'initiated_by')) {
                $table->string('initiated_by')->default('cashier')->after('created_by');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('bill_payment_transactions', 'initiated_by')) {
            Schema::table('bill_payment_transactions', function (Blueprint $table) {
                $table->dropColumn('initiated_by');
            });
        }

        Schema::dropIfExists('parent_communication_logs');
        Schema::dropIfExists('parent_messages');
        Schema::dropIfExists('parent_accounts');
    }
};
