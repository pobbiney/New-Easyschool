<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_extra_links', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('link_id');
            $table->primary(['user_id', 'link_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_extra_links');
    }
};
