<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        // Registrasi middleware 'role' untuk digunakan dalam routes
        Route::aliasMiddleware('role', \App\Http\Middleware\RoleMiddleware::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Hapus atau komen baris ini jika tidak diperlukan
        // $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
        
        Paginator::useBootstrapFive();
    }
}
