<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->string('status')->default('Active');
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
                $table->date('expense_date');
                $table->decimal('amount', 12, 2);
                $table->string('payee');
                $table->string('payment_method', 40);
                $table->string('reference')->nullable();
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('academic_year_id')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->index(['expense_date', 'expense_category_id']);
            });

            if (Schema::hasTable('academic_years')) {
                Schema::table('expenses', function (Blueprint $table) {
                    $table->foreign('academic_year_id')->references('id')->on('academic_years')->nullOnDelete();
                });
            }
        }

        $userId = (int) (DB::table('users')->min('id') ?: 1);
        $defaults = [
            'Utilities' => 'Electricity, water, internet, and similar bills.',
            'Teaching materials' => 'Classroom resources and teaching aids.',
            'Maintenance' => 'Repairs, painting, and facility upkeep.',
            'Transport' => 'Fuel, vehicle hire, and travel.',
            'Feeding' => 'Food, kitchen, and canteen costs.',
            'Salaries/allowances' => 'Staff pay, allowances, and related costs.',
            'Stationery' => 'Office and classroom stationery.',
            'Events' => 'Speech day, sports, and other programmes.',
            'Miscellaneous' => 'Other school spending that does not fit above.',
        ];

        foreach ($defaults as $name => $description) {
            $exists = DB::table('expense_categories')->where('name', $name)->exists();
            if ($exists) {
                continue;
            }

            DB::table('expense_categories')->insert([
                'name' => $name,
                'description' => $description,
                'status' => 'Active',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
