<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('status', 20)->default('Active')->after('academic_year_id');
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');

            $table->index(['status', 'expense_date'], 'expenses_status_date_index');
        });

        Schema::create('expense_vault_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->restrictOnDelete();
            $table->unsignedInteger('version');
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->string('payee', 150);
            $table->string('payment_method', 40);
            $table->string('reference', 100)->nullable();
            $table->unsignedBigInteger('academic_year_id')->nullable();
            $table->string('status', 20)->default('Active');
            $table->uuid('request_token')->unique();
            $table->text('change_reason')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('ended_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['expense_id', 'version'], 'expense_posting_version_unique');
            $table->index(['expense_id', 'status'], 'expense_posting_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_vault_postings');

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_status_date_index');
            $table->dropColumn(['status', 'cancelled_at', 'cancelled_by', 'cancellation_reason']);
        });
    }
};
