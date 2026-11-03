<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_bills', 'academic_year_id')) {
            Schema::table('student_bills', function (Blueprint $table) {
                $table->foreignId('academic_year_id')->nullable()->after('billing_item_id')->constrained('academic_years');
                $table->foreignId('academic_term_id')->nullable()->after('academic_year_id')->constrained('academic_terms');
            });
        }

        DB::statement('
            UPDATE student_bills sb
            INNER JOIN category_bill_setups cbs ON cbs.id = sb.category_bill_setup_id
            SET sb.academic_year_id = cbs.academic_year_id,
                sb.academic_term_id = cbs.academic_term_id
            WHERE sb.academic_year_id IS NULL OR sb.academic_term_id IS NULL
        ');

        $duplicateGroups = DB::select('
            SELECT sb.student_id, sb.billing_item_id, cbs.academic_year_id, cbs.academic_term_id
            FROM student_bills sb
            INNER JOIN category_bill_setups cbs ON cbs.id = sb.category_bill_setup_id
            GROUP BY sb.student_id, sb.billing_item_id, cbs.academic_year_id, cbs.academic_term_id
            HAVING COUNT(*) > 1
        ');

        foreach ($duplicateGroups as $group) {
            $billIds = DB::table('student_bills as sb')
                ->join('category_bill_setups as cbs', 'cbs.id', '=', 'sb.category_bill_setup_id')
                ->where('sb.student_id', $group->student_id)
                ->where('sb.billing_item_id', $group->billing_item_id)
                ->where('cbs.academic_year_id', $group->academic_year_id)
                ->where('cbs.academic_term_id', $group->academic_term_id)
                ->orderByDesc('sb.amount_paid')
                ->orderByDesc('sb.id')
                ->pluck('sb.id');

            $keeperId = $billIds->first();
            $duplicateIds = $billIds->slice(1)->values();

            if (! $keeperId || $duplicateIds->isEmpty()) {
                continue;
            }

            $keeper = DB::table('student_bills')->where('id', $keeperId)->first();

            foreach ($duplicateIds as $duplicateId) {
                $duplicate = DB::table('student_bills')->where('id', $duplicateId)->first();

                DB::table('bill_payment_allocations')
                    ->where('student_bill_id', $duplicateId)
                    ->update(['student_bill_id' => $keeperId]);

                DB::table('student_bills')
                    ->where('id', $keeperId)
                    ->update([
                        'amount_paid' => (float) $keeper->amount_paid + (float) $duplicate->amount_paid,
                        'amount_due' => max((float) $keeper->amount_due, (float) $duplicate->amount_due),
                        'updated_at' => now(),
                    ]);

                $keeper = DB::table('student_bills')->where('id', $keeperId)->first();
                DB::table('student_bills')->where('id', $duplicateId)->delete();
            }

            $keeper = DB::table('student_bills')->where('id', $keeperId)->first();
            $balance = max((float) $keeper->amount_due - (float) $keeper->amount_paid, 0);
            $status = 'Pending';
            if ($balance <= 0 && (float) $keeper->amount_paid > 0) {
                $status = 'Paid';
            } elseif ((float) $keeper->amount_paid > 0) {
                $status = 'Partial';
            }

            DB::table('student_bills')
                ->where('id', $keeperId)
                ->update([
                    'balance' => $balance,
                    'status' => $status,
                    'updated_at' => now(),
                ]);
        }

        $indexes = collect(DB::select('SHOW INDEX FROM student_bills'))->pluck('Key_name')->unique();

        if (! $indexes->contains('student_bills_period_unique')) {
            Schema::table('student_bills', function (Blueprint $table) {
                $table->unique(
                    ['student_id', 'billing_item_id', 'academic_year_id', 'academic_term_id'],
                    'student_bills_period_unique'
                );
            });
        }

        if ($indexes->contains('student_bills_unique')) {
            Schema::table('student_bills', function (Blueprint $table) {
                $table->dropUnique('student_bills_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->unique(
                ['student_id', 'category_bill_setup_id', 'billing_item_id'],
                'student_bills_unique'
            );
            $table->dropUnique('student_bills_period_unique');
            $table->dropConstrainedForeignId('academic_term_id');
            $table->dropConstrainedForeignId('academic_year_id');
        });
    }
};
