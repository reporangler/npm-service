<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('user-token', function() {
            $user = request()->user();
            return $user ? $user->token : null;
        });
    }

    public function boot()
    {
    }
}
