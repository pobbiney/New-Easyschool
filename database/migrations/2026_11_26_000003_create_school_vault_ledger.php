<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_vaults', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 40)->unique();
            $table->string('name', 100);
            $table->char('currency', 3)->default('GHS');
            $table->string('status', 20)->default('Active');
            $table->timestamps();
        });

        Schema::create('school_vault_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_vault_id')->constrained('school_vaults')->restrictOnDelete();
            $table->string('transaction_number', 50)->unique();
            $table->string('direction', 10);
            $table->string('transaction_type', 50);
            $table->decimal('amount', 12, 2);
            $table->string('source_type', 150);
            $table->unsignedBigInteger('source_id');
            $table->string('description', 500);
            $table->dateTime('transaction_date');
            $table->foreignId('recorded_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('reversal_of_id')->nullable()->constrained('school_vault_transactions')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(
                ['school_vault_id', 'transaction_type', 'source_type', 'source_id'],
                'school_vault_source_unique'
            );
            $table->unique('reversal_of_id', 'school_vault_reversal_unique');
            $table->index(['school_vault_id', 'transaction_date'], 'school_vault_date_index');
            $table->index(['school_vault_id', 'direction'], 'school_vault_direction_index');
        });

        DB::table('school_vaults')->insert([
            'account_code' => 'SCHOOL_VAULT',
            'name' => 'School Vault',
            'currency' => 'GHS',
            'status' => 'Active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_vault_transactions');
        Schema::dropIfExists('school_vaults');
    }
};
