<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        parent::boot();
        
        // Fix duplicate route names
        Route::resourceVerbs([
            'create' => 'create',
            'edit' => 'edit',
        ]);
        
    }
}
