<?php

namespace App\Support;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class AcademicPeriodDefaults
{
    public static function yearId(?Request $request = null, string $key = 'academic_year_id'): ?int
    {
        if ($request && $request->has($key)) {
            $value = $request->input($key);

            return ($value !== '' && $value !== null) ? (int) $value : null;
        }

        return SchoolSetting::current()->defaultAcademicYearId();
    }

    public static function termId(?Request $request = null, string $key = 'academic_term_id'): ?int
    {
        if ($request && $request->has($key)) {
            $value = $request->input($key);

            return ($value !== '' && $value !== null) ? (int) $value : null;
        }

        return SchoolSetting::current()->defaultAcademicTermId();
    }

    public static function forFrontend(?Request $request = null): array
    {
        $school = SchoolSetting::current()->load(['defaultAcademicYear', 'defaultAcademicTerm']);

        $yearId = self::yearId($request);
        $termId = self::termId($request);

        $year = $yearId
            ? \App\Models\AcademicYear::query()->find($yearId)
            : $school->defaultAcademicYear;

        $term = $termId
            ? \App\Models\AcademicTerm::query()->find($termId)
            : $school->defaultAcademicTerm;

        return [
            'year_id' => $yearId,
            'term_id' => $termId,
            'year_name' => $year?->name,
            'term_name' => $term?->name,
        ];
    }
}
