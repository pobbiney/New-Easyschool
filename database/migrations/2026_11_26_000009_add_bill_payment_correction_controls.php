<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('notes')->index();
            $table->timestamp('voided_at')->nullable()->after('status');
            $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            $table->string('voided_actor')->nullable()->after('voided_by');
            $table->text('void_reason')->nullable()->after('voided_actor');
        });
    }

    public function down(): void
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'voided_at', 'voided_by', 'voided_actor', 'void_reason']);
        });
    }
};
