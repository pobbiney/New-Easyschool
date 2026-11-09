<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('class_teacher_assignments')) {
            Schema::create('class_teacher_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('school_class_id');
                $table->unsignedBigInteger('staff_id');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->foreign('school_class_id')->references('id')->on('school_classes')->cascadeOnDelete();
                $table->foreign('staff_id')->references('id')->on('staff')->cascadeOnDelete();
                $table->unique(['school_class_id', 'staff_id'], 'class_teacher_assignments_class_staff_unique');
                $table->unique('staff_id', 'class_teacher_assignments_staff_unique');
            });
        }

        if (! Schema::hasColumn('school_classes', 'class_teacher_id')) {
            return;
        }

        $now = now();
        $rows = DB::table('school_classes')
            ->whereNotNull('class_teacher_id')
            ->get(['id', 'class_teacher_id', 'created_by', 'updated_by']);

        foreach ($rows as $row) {
            $exists = DB::table('class_teacher_assignments')
                ->where('school_class_id', $row->id)
                ->where('staff_id', $row->class_teacher_id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('class_teacher_assignments')->insert([
                'school_class_id' => $row->id,
                'staff_id' => $row->class_teacher_id,
                'created_by' => $row->created_by,
                'updated_by' => $row->updated_by,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->dropConstraintsOnClassTeacherId();

        Schema::table('school_classes', function (Blueprint $table) {
            if (Schema::hasColumn('school_classes', 'class_teacher_id')) {
                $table->dropColumn('class_teacher_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('school_classes', 'class_teacher_id')) {
            Schema::table('school_classes', function (Blueprint $table) {
                $table->unsignedBigInteger('class_teacher_id')->nullable()->after('status');
            });

            if (Schema::hasColumn('school_classes', 'school_id')) {
                Schema::table('school_classes', function (Blueprint $table) {
                    $table->unique(['school_id', 'class_teacher_id'], 'school_classes_school_id_class_teacher_id_unique');
                });
            } else {
                Schema::table('school_classes', function (Blueprint $table) {
                    $table->unique('class_teacher_id');
                });
            }
        }

        if (Schema::hasTable('class_teacher_assignments')) {
            $assignments = DB::table('class_teacher_assignments')
                ->orderBy('id')
                ->get(['school_class_id', 'staff_id']);

            $seen = [];
            foreach ($assignments as $assignment) {
                if (isset($seen[$assignment->school_class_id])) {
                    continue;
                }

                $seen[$assignment->school_class_id] = true;
                DB::table('school_classes')
                    ->where('id', $assignment->school_class_id)
                    ->update(['class_teacher_id' => $assignment->staff_id]);
            }
        }

        Schema::dropIfExists('class_teacher_assignments');
    }

    private function dropConstraintsOnClassTeacherId(): void
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'school_classes'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
              AND CONSTRAINT_NAME LIKE '%class_teacher%'
        ");

        foreach ($foreignKeys as $foreignKey) {
            DB::statement('ALTER TABLE school_classes DROP FOREIGN KEY `'.$foreignKey->CONSTRAINT_NAME.'`');
        }

        $uniques = DB::select("
            SELECT DISTINCT INDEX_NAME
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'school_classes'
              AND COLUMN_NAME = 'class_teacher_id'
              AND NON_UNIQUE = 0
              AND INDEX_NAME != 'PRIMARY'
        ");

        foreach ($uniques as $unique) {
            DB::statement('ALTER TABLE school_classes DROP INDEX `'.$unique->INDEX_NAME.'`');
        }
    }
};
