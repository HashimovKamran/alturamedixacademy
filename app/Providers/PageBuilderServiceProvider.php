<?php

namespace App\Providers;

use App\Console\Commands\SeedPageBuilderDemo;
use App\PageBuilder\Services\AssetReferenceScanner;
use App\PageBuilder\Services\DraftService;
use App\PageBuilder\Services\PageManager;
use App\PageBuilder\Services\PageWorkflow;
use App\PageBuilder\Services\PublishService;
use App\PageBuilder\Support\ComponentCatalog;
use App\PageBuilder\Support\DocumentValidationService;
use App\PageBuilder\Support\DocumentValidator;
use App\PageBuilder\Support\HtmlSanitizer;
use App\PageBuilder\Support\SectionZonePolicy;
use App\PageBuilder\Support\SlugNormalizer;
use App\PageBuilder\Support\TemplateCatalog;
use App\PageBuilder\Support\ThemeSettingsValidator;
use Illuminate\Support\ServiceProvider;

final class PageBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ComponentCatalog::class);
        $this->app->singleton(HtmlSanitizer::class);
        $this->app->singleton(SlugNormalizer::class);
        $this->app->singleton(TemplateCatalog::class);
        $this->app->singleton(SectionZonePolicy::class);
        $this->app->singleton(ThemeSettingsValidator::class);
        $this->app->singleton(AssetReferenceScanner::class);

        // Document validation keeps request-specific asset references while normalizing a document.
        // Resolve it per use rather than sharing mutable state across long-lived workers.
        $this->app->bind(DocumentValidator::class);
        $this->app->bind(DocumentValidationService::class);
        $this->app->bind(DraftService::class);
        $this->app->bind(PublishService::class);
        $this->app->bind(PageManager::class);
        $this->app->bind(PageWorkflow::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SeedPageBuilderDemo::class]);
        }
    }
}
