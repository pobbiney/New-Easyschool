<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('id_card_settings')) {
            return;
        }

        Schema::create('id_card_settings', function (Blueprint $table) {
            $table->id();
            $table->string('card_title', 120)->default('Student Identity Card');
            $table->string('header_text', 180)->nullable();
            $table->string('footer_text', 180)->nullable();
            $table->string('accent_color', 20)->default('#25A194');
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_id_number')->default(true);
            $table->boolean('show_class')->default(true);
            $table->boolean('show_expiry')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card_settings');
    }
};
