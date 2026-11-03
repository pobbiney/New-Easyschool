<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('last_promotion_from_class_id')
                ->nullable()
                ->after('school_class_id')
                ->constrained('school_classes')
                ->nullOnDelete();
            $table->string('last_promotion_type', 20)->nullable()->after('last_promotion_from_class_id');
            $table->timestamp('last_promoted_at')->nullable()->after('last_promotion_type');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_promotion_from_class_id');
            $table->dropColumn(['last_promotion_type', 'last_promoted_at']);
        });
    }
};
