<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'bank_account_number',
                'bank_name',
                'ifsc_code',
                'national_id_number',
                'previous_school_name',
                'previous_school_address',
                'permanent_address',
                'hostel',
                'room_no',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('national_id_number')->nullable();
            $table->string('previous_school_name')->nullable();
            $table->string('previous_school_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('hostel')->nullable();
            $table->string('room_no')->nullable();
        });
    }
};
