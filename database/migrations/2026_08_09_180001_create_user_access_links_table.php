<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_extra_links') && ! Schema::hasTable('user_access_links')) {
            Schema::rename('user_extra_links', 'user_access_links');
        }

        if (! Schema::hasTable('user_access_links')) {
            Schema::create('user_access_links', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedInteger('link_id');
                $table->timestamps();
                $table->primary(['user_id', 'link_id']);
            });

            return;
        }

        if (! Schema::hasColumn('user_access_links', 'created_at')) {
            Schema::table('user_access_links', function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_access_links') && ! Schema::hasTable('user_extra_links')) {
            Schema::rename('user_access_links', 'user_extra_links');
        }

        if (Schema::hasTable('user_access_links') && Schema::hasColumn('user_access_links', 'created_at')) {
            Schema::table('user_access_links', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
    }
};
