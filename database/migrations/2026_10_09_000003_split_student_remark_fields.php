<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            $table->string('attitude')->nullable()->after('academic_term_id');
            $table->string('conduct')->nullable()->after('attitude');
            $table->string('interest')->nullable()->after('conduct');
            $table->string('class_teacher_remark', 500)->nullable()->after('interest');
            $table->string('headteacher_signature')->nullable()->after('class_teacher_remark');
        });

        if (Schema::hasColumn('student_remarks', 'remark')) {
            DB::table('student_remarks')
                ->whereNotNull('remark')
                ->where('remark', '!=', '')
                ->update(['class_teacher_remark' => DB::raw('remark')]);

            Schema::table('student_remarks', function (Blueprint $table) {
                $table->dropColumn('remark');
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_remarks', function (Blueprint $table) {
            $table->text('remark')->nullable()->after('academic_term_id');
        });

        DB::table('student_remarks')->update([
            'remark' => DB::raw('class_teacher_remark'),
        ]);

        Schema::table('student_remarks', function (Blueprint $table) {
            $table->dropColumn([
                'attitude',
                'conduct',
                'interest',
                'class_teacher_remark',
                'headteacher_signature',
            ]);
        });
    }
};
