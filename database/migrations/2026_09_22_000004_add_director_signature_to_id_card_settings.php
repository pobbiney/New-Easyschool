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
            if (! Schema::hasColumn('id_card_settings', 'director_name')) {
                $table->string('director_name', 120)->nullable()->after('footer_text');
            }
            if (! Schema::hasColumn('id_card_settings', 'director_signature_path')) {
                $table->string('director_signature_path')->nullable()->after('director_name');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('id_card_settings')) {
            return;
        }

        Schema::table('id_card_settings', function (Blueprint $table) {
            if (Schema::hasColumn('id_card_settings', 'director_signature_path')) {
                $table->dropColumn('director_signature_path');
            }
            if (Schema::hasColumn('id_card_settings', 'director_name')) {
                $table->dropColumn('director_name');
            }
        });
    }
};
