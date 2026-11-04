<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(prepend: [
            \App\Http\Middleware\ForceRequestRootUrl::class,
        ]);

        $middleware->redirectGuestsTo(function () {
            if (request()->is('parent') || request()->is('parent/*')) {
                return '/parent/login';
            }

            return '/';
        });

        $middleware->alias([
            'teacher' => \App\Http\Middleware\EnsureTeacher::class,
            'teacher.owns.class' => \App\Http\Middleware\EnsureTeacherOwnsClass::class,
            'parent' => \App\Http\Middleware\EnsureParent::class,
            'parent.owns.student' => \App\Http\Middleware\EnsureParentOwnsStudent::class,
            'staff.auth' => \App\Http\Middleware\EnsureStaffAuthenticated::class,
            'menu.access' => \App\Http\Middleware\EnsureMenuAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
