<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::defaultView('vendor.pagination.custom');

        view()->composer(['layouts.main', 'welcome', 'partials.interests-modal', 'partials.categories-bar', 'users.settings.profile'], function ($view) {
            $view->with('categories', \App\Models\Category::whereNull('parent_id')->get());
        });

        view()->composer('*', function ($view) {
            if (auth()->check() && auth()->user()->account_type === 'partner') {
                $categoryIds = auth()->user()->interests()->pluck('categories.id')->toArray();
                $partnerServicesOptions = \App\Models\Service::where('user_id', 1)
                    ->whereIn('category_id', $categoryIds)
                    ->get();
                $view->with('partnerServicesOptions', $partnerServicesOptions);
            }
        });

        require base_path('routes/channels.php');
    }
}
