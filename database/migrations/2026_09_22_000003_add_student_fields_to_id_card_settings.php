<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('id_card_settings')) {
            return;
        }

        Schema::table('id_card_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('id_card_settings', 'student_fields')) {
                $table->json('student_fields')->nullable()->after('show_expiry');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('id_card_settings')) {
            return;
        }

        Schema::table('id_card_settings', function (Blueprint $table) {
            if (Schema::hasColumn('id_card_settings', 'student_fields')) {
                $table->dropColumn('student_fields');
            }
        });
    }
};
