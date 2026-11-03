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
            if (! Schema::hasColumn('id_card_settings', 'employee_fields')) {
                $table->json('employee_fields')->nullable()->after('student_fields');
            }
            if (! Schema::hasColumn('id_card_settings', 'employee_card_title')) {
                $table->string('employee_card_title', 120)->nullable()->after('card_title');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('id_card_settings')) {
            return;
        }

        Schema::table('id_card_settings', function (Blueprint $table) {
            if (Schema::hasColumn('id_card_settings', 'employee_fields')) {
                $table->dropColumn('employee_fields');
            }
            if (Schema::hasColumn('id_card_settings', 'employee_card_title')) {
                $table->dropColumn('employee_card_title');
            }
        });
    }
};
