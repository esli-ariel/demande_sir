<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
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
        //
    }

    /**
     * Page par défaut après connexion
     */
    public const HOME = '/dashboard';

    /**
     * Redirection personnalisée selon le rôle de l'utilisateur
     */
    protected function redirectTo($request)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return '/admin';
            }
            if ($user->hasRole('demandeur')) {
                return '/dashboard/demandeur';
            }
            if ($user->hasRole('exploitant')) {
                return '/dashboard/exploitation';
            }
            if ($user->hasRole('dts')) {
                return '/dashboard/dts';
            }
            if ($user->hasRole('structure_specialisee')) {
                return '/dashboard/structure';
            }
            if ($user->hasRole('controle_avancee')) {
                return '/dashboard/controle';
            }
            if ($user->hasRole('service_technique')) {
                return '/dashboard/service';
            }
            if ($user->hasRole('reception')) {
                return '/dashboard/reception';
            }
        }

        // Si non connecté
        return '/login';
    }
}
