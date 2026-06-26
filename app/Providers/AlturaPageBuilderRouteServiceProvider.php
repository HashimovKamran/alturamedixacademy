<?php

namespace App\Providers;

use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AlturaPageBuilderRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware(['web', EnsureAdminAuthenticated::class])
            ->prefix('admin')
            ->name('admin.')
            ->group(base_path('routes/admin-page-builder-react.php'));
    }
}
