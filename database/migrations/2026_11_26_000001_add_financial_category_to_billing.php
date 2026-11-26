<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $feedingCandidates = DB::table('billing_items')
            ->whereIn(DB::raw('LOWER(TRIM(name))'), ['feeding', 'feeding fee', 'feeding fees'])
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($feedingCandidates->count() > 1) {
            $matches = $feedingCandidates
                ->map(fn ($item) => '#'.$item->id.' '.$item->name)
                ->implode(', ');

            throw new RuntimeException(
                'Multiple potential Feeding Fee billing items were found: '.$matches.'. Resolve them before rerunning this migration.'
            );
        }

        Schema::table('billing_items', function (Blueprint $table) {
            $table->string('financial_category', 30)
                ->default('school_fees')
                ->after('is_compulsory');
        });

        Schema::table('student_bills', function (Blueprint $table) {
            $table->string('financial_category', 30)
                ->default('school_fees')
                ->after('billing_item_id');
        });

        if ($feedingCandidates->count() === 1) {
            $feedingItemId = $feedingCandidates->first()->id;

            DB::table('billing_items')
                ->where('id', $feedingItemId)
                ->update([
                    'financial_category' => 'feeding_fee',
                    'is_compulsory' => false,
                ]);

            DB::table('student_bills')
                ->where('billing_item_id', $feedingItemId)
                ->update(['financial_category' => 'feeding_fee']);
        }
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropColumn('financial_category');
        });

        Schema::table('billing_items', function (Blueprint $table) {
            $table->dropColumn('financial_category');
        });
    }
};
