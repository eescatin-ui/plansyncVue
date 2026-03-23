<?php

namespace App\Providers;

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

    protected $policies = [
    ClassSchedule::class => ClassSchedulePolicy::class,
];
    public function boot()
{
    if ($this->app->environment('local')) {
        // Disable route caching in development
        config(['app.debug' => true]);
    }
}

    
}
