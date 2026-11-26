<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_cash_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_vault_id')->constrained('school_vaults')->restrictOnDelete();
            $table->date('reconciliation_date');
            $table->decimal('cash_collections', 14, 2);
            $table->decimal('cash_expenditure', 14, 2);
            $table->decimal('actual_cash_counted', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reconciled_at');
            $table->timestamps();

            $table->unique(['school_vault_id', 'reconciliation_date'], 'vault_cash_reconciliation_day_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_cash_reconciliations');
    }
};
