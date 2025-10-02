<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Route;


class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
    public const HOME = '/dashboard';

protected function redirectTo($request)
{ if (Auth::check()) {
        if (Auth::user()->hasRole('admin')) {
            return '/admin';
        } elseif (Auth::user()->hasRole('responsable_S')) {
            return '/dashboard';
        } elseif (Auth::user()->hasRole('service_technique')) {
            return '/dashboard';
        } elseif (Auth::user()->hasRole('demandeur')) {
            return '/dashboard';
        }
    }
     return '/login';

    }

   
}

