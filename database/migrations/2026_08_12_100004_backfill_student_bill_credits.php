<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $overpayments = DB::table('student_bills')
            ->select('student_id', DB::raw('SUM(GREATEST(amount_paid - amount_due, 0)) as hidden_credit'))
            ->groupBy('student_id')
            ->having('hidden_credit', '>', 0)
            ->get();

        foreach ($overpayments as $row) {
            $credit = round((float) $row->hidden_credit, 2);

            if ($credit <= 0) {
                continue;
            }

            DB::table('students')
                ->where('id', $row->student_id)
                ->update(['credit_balance' => $credit]);

            DB::table('student_bill_credit_transactions')->insert([
                'student_id' => $row->student_id,
                'type' => 'credit',
                'amount' => $credit,
                'balance_after' => $credit,
                'source' => 'adjustment',
                'bill_payment_id' => null,
                'notes' => 'Backfilled from historical bill overpayments.',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('student_bill_credit_transactions')
            ->where('source', 'adjustment')
            ->where('notes', 'Backfilled from historical bill overpayments.')
            ->delete();

        DB::table('students')->update(['credit_balance' => 0]);
    }
};
