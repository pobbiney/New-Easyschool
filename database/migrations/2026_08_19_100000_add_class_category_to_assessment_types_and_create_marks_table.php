<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(Schema::getIndexes('assessment_types'))->pluck('name');

        if ($indexes->contains('assessment_types_slug_unique')) {
            Schema::table('assessment_types', function (Blueprint $table) {
                $table->dropUnique('assessment_types_slug_unique');
            });
        } elseif ($indexes->contains('slug')) {
            Schema::table('assessment_types', function (Blueprint $table) {
                $table->dropUnique('slug');
            });
        } else {
            $slugUnique = $indexes->first(fn ($name) => is_string($name) && str_contains($name, 'slug') && str_contains($name, 'unique'));

            if ($slugUnique) {
                Schema::table('assessment_types', function (Blueprint $table) use ($slugUnique) {
                    $table->dropUnique($slugUnique);
                });
            }
        }

        if (! Schema::hasColumn('assessment_types', 'class_category_id')) {
            Schema::table('assessment_types', function (Blueprint $table) {
                $table->foreignId('class_category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('class_categories')
                    ->cascadeOnDelete();
            });
        }

        $templates = DB::table('assessment_types')->whereNull('class_category_id')->get();
        $categories = DB::table('class_categories')
            ->where('status', 'Active')
            ->orderBy('id')
            ->get();

        if ($templates->isNotEmpty() && $categories->isNotEmpty()) {
            $firstCategory = $categories->first();

            DB::table('assessment_types')
                ->whereNull('class_category_id')
                ->update(['class_category_id' => $firstCategory->id]);

            $now = now();

            foreach ($categories->skip(1) as $category) {
                foreach ($templates as $template) {
                    $exists = DB::table('assessment_types')
                        ->where('class_category_id', $category->id)
                        ->where('slug', $template->slug)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('assessment_types')->insert([
                        'class_category_id' => $category->id,
                        'name' => $template->name,
                        'slug' => $template->slug,
                        'category' => $template->category ?? 'class_assessment',
                        'sort_order' => $template->sort_order,
                        'max_number' => $template->max_number ?? 1,
                        'total_score' => $template->total_score ?? 100,
                        'status' => $template->status,
                        'created_by' => $template->created_by ?? 1,
                        'updated_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $indexes = collect(Schema::getIndexes('assessment_types'))->pluck('name');

        Schema::table('assessment_types', function (Blueprint $table) use ($indexes) {
            if (! $indexes->contains('assessment_types_category_name_unique')) {
                $table->unique(['class_category_id', 'name'], 'assessment_types_category_name_unique');
            }
            if (! $indexes->contains('assessment_types_category_slug_unique')) {
                $table->unique(['class_category_id', 'slug'], 'assessment_types_category_slug_unique');
            }
        });

        if (! Schema::hasTable('class_course_assessment_marks')) {
            Schema::create('class_course_assessment_marks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
                $table->foreignId('assessment_type_id')->constrained('assessment_types')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
                $table->foreignId('academic_term_id')->constrained('academic_terms')->cascadeOnDelete();
                $table->decimal('total_score', 8, 2);
                $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                $table->unique(
                    ['school_class_id', 'course_id', 'assessment_type_id', 'academic_year_id', 'academic_term_id'],
                    'class_course_assess_marks_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('class_course_assessment_marks');

        Schema::table('assessment_types', function (Blueprint $table) {
            $table->dropUnique('assessment_types_category_name_unique');
            $table->dropUnique('assessment_types_category_slug_unique');
        });

        $keepCategoryId = DB::table('assessment_types')
            ->whereNotNull('class_category_id')
            ->orderBy('class_category_id')
            ->value('class_category_id');

        if ($keepCategoryId) {
            DB::table('assessment_types')
                ->where('class_category_id', '!=', $keepCategoryId)
                ->delete();
        }

        Schema::table('assessment_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_category_id');
            $table->unique('slug');
        });
    }
};
