<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssessmentType extends Model
{

    public const CATEGORY_CLASS = 'class_assessment';

    public const CATEGORY_EXAMINATION = 'examination_assessment';

    public const CATEGORIES = [
        self::CATEGORY_CLASS => 'Class Assessment',
        self::CATEGORY_EXAMINATION => 'Examination Assessment',
    ];

    protected $fillable = [
        'class_category_id',
        'name',
        'slug',
        'category',
        'sort_order',
        'max_number',
        'total_score',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'max_number' => 'integer',
        'total_score' => 'decimal:2',
    ];

    public function classCategory()
    {
        return $this->belongsTo(ClassCategory::class, 'class_category_id');
    }

    public function courseMarks()
    {
        return $this->hasMany(ClassCourseAssessmentMark::class, 'assessment_type_id');
    }

    public function assessments()
    {
        return $this->hasMany(AcademicAssessment::class, 'type', 'slug');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeForClassCategory($query, ?int $classCategoryId)
    {
        if ($classCategoryId) {
            return $query->where('class_category_id', $classCategoryId);
        }

        return $query->whereNull('class_category_id');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucwords(str_replace('_', ' ', (string) $this->category));
    }

    public static function categoryOptions(): array
    {
        return self::CATEGORIES;
    }

    public static function activeSlugs(): array
    {
        return static::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('slug')
            ->unique()
            ->values()
            ->all();
    }

    public function isInUse(): bool
    {
        if (isset($this->assessments_count)) {
            return (int) $this->assessments_count > 0;
        }

        return $this->usageQuery()->exists();
    }

    public function usageQuery()
    {
        return AcademicAssessment::query()
            ->where('type', $this->slug)
            ->whereHas('schoolClass', function ($query) {
                $query->where('class_category_id', $this->class_category_id);
            });
    }

    public static function makeUniqueSlug(string $name, int $classCategoryId, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '_');
        $slug = $base !== '' ? $base : 'type';
        $counter = 2;

        while (static::query()
            ->where('class_category_id', $classCategoryId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'_'.$counter;
            $counter++;
        }

        return $slug;
    }

    public static function usageCountSubquery()
    {
        return AcademicAssessment::query()
            ->selectRaw('count(*)')
            ->whereColumn('academic_assessments.type', 'assessment_types.slug')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('school_classes')
                    ->whereColumn('school_classes.id', 'academic_assessments.school_class_id')
                    ->whereColumn('school_classes.class_category_id', 'assessment_types.class_category_id');
            });
    }
}
