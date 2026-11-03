<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('docs:user-manual', function () {
    ini_set('memory_limit', '1024M');

    $school = \App\Models\SchoolSetting::current();
    $path = public_path('docs/EasySchool-User-Manual.pdf');

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    \Barryvdh\DomPDF\Facade\Pdf::loadView('docs.user-manual', [
        'schoolName' => $school->name ?: 'EasySchool',
        'generatedAt' => now()->format('d F Y'),
    ])->setPaper('a4', 'portrait')->save($path);

    $this->info('Wrote '.$path);
})->purpose('Generate the EasySchool end-user manual PDF');

Artisan::command('parent:sync-accounts', function () {
    $count = app(\App\Services\ParentPortal\ParentAccountService::class)->syncAllActiveStudents();
    $this->info("Synced parent accounts for {$count} active student record(s).");
})->purpose('Create or update parent portal accounts from active students');
