<?php

namespace App\Models;

use App\Support\MediaUrl;
use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'name',
        'motto',
        'address',
        'phone',
        'email',
        'website',
        'logo_path',
        'default_academic_year_id',
        'default_academic_term_id',
        'updated_by',
    ];

    public static function current()
    {
        return static::firstOrCreate([]);
    }

    public static function dummyLogoPath(): string
    {
        return 'assets/images/logo-icon.png';
    }

    public function logoUrl(): string
    {
        $path = $this->uploadedLogoPath();

        if ($path === null) {
            return asset(static::dummyLogoPath());
        }

        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return $path;
        }

        return asset($path);
    }

    public function logoFilePath(): string
    {
        $path = $this->uploadedLogoPath();

        if ($path === null || preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return str_replace('\\', '/', public_path(static::dummyLogoPath()));
        }

        return str_replace('\\', '/', public_path($path));
    }

    public function uploadedLogoPath(): ?string
    {
        if (! filled($this->logo_path)) {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', trim((string) $this->logo_path)), '/');

        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '//')) {
            return MediaUrl::resolve($path);
        }

        return is_file(public_path($path)) ? $path : null;
    }

    public function defaultAcademicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'default_academic_year_id');
    }

    public function defaultAcademicTerm()
    {
        return $this->belongsTo(AcademicTerm::class, 'default_academic_term_id');
    }

    public function defaultAcademicYearId(): ?int
    {
        $year = $this->defaultAcademicYear;

        if ($year && $year->status === 'Active') {
            return (int) $year->id;
        }

        return null;
    }

    public function defaultAcademicTermId(): ?int
    {
        $term = $this->defaultAcademicTerm;

        if ($term && $term->status === 'Active') {
            return (int) $term->id;
        }

        return null;
    }

    public function codeInitials(): string
    {
        $name = str_replace(["'", '’', '`'], '', trim((string) $this->name));
        $words = preg_split('/[^A-Za-z0-9]+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $skip = ['the', 'of', 'and', 'for', 'a', 'an', 'at', 'in', 'to'];
        $words = array_values(array_filter(
            $words,
            fn ($word) => ! in_array(strtolower($word), $skip, true)
        ));

        if ($words === []) {
            return 'SCH';
        }

        $letters = '';
        foreach ($words as $word) {
            $letters .= strtoupper(substr($word, 0, 1));
            if (strlen($letters) >= 4) {
                break;
            }
        }

        if (strlen($letters) < 2) {
            $letters = strtoupper(substr($words[0].'SCH', 0, 3));
        }

        return $letters;
    }
}
