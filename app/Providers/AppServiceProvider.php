<?php

namespace App\Providers;

use App\Models\SchoolSetting;
use App\Models\User;
use App\Support\Navigation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale'));

        Gate::before(function (User $user) {
            if ($user->isAdmin() && $user->is_active) {
                return true;
            }

            return null;
        });

        View::composer('layouts.app', function ($view) {
            $user = auth()->user();
            $view->with('navItems', $user ? Navigation::items($user) : []);
            $view->with('schoolName', SchoolSetting::displayName());
        });

        View::composer('layouts.guest', function ($view) {
            $view->with('schoolName', SchoolSetting::displayName());
        });
    }
}
