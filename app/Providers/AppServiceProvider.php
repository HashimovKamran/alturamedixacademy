<?php

namespace App\Providers;

use App\Support\Database\EnsureDatabaseExists;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::directive('pbSchema', fn ($expression = null) => '<?php /* page builder schema */ ?>');

        if (!$this->app->runningInConsole()) {
            return;
        }

        $command = $_SERVER['argv'][1] ?? '';

        if (in_array($command, ['migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:reset'], true)) {
            EnsureDatabaseExists::forDefaultConnection();
        }
    }
}
