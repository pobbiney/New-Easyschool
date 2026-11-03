<?php

namespace App\Providers;

use App\Support\AcademicPeriodDefaults;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            $request = $this->app['request'];
            URL::forceRootUrl($request->root());
            URL::forceScheme($request->getScheme());
        }

        // Share with all views — child @section/@include content does not inherit layout composers.
        View::composer('*', function ($view) {
            static $defaults = null;

            if ($defaults === null) {
                $emptyDefaults = [
                    'defaultAcademicYearId' => null,
                    'defaultAcademicTermId' => null,
                    'defaultAcademicYearName' => null,
                    'defaultAcademicTermName' => null,
                ];

                try {
                    $period = AcademicPeriodDefaults::forFrontend();
                    $defaults = [
                        'defaultAcademicYearId' => $period['year_id'],
                        'defaultAcademicTermId' => $period['term_id'],
                        'defaultAcademicYearName' => $period['year_name'],
                        'defaultAcademicTermName' => $period['term_name'],
                    ];
                } catch (\Throwable $e) {
                    $defaults = $emptyDefaults;
                }
            }

            $view->with($defaults);
        });
    }
}
