<?php

namespace App\Providers;

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
        View::composer('components.sidebar', function ($view) {
            if (! auth()->check() || array_key_exists('totalCount', $view->getData())) {
                return;
            }

            $user = auth()->user();

            $view->with([
                'totalCount' => $user->tasks()->count(),
                'activeCount' => $user->tasks()->active()->count(),
                'completedCount' => $user->tasks()->completed()->count(),
                'todayCount' => $user->tasks()->today()->active()->count(),
            ]);
        });
    }
}
