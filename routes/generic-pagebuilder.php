<?php

use App\Http\Controllers\PageBuilder\ApiController;
use App\Http\Controllers\PageBuilder\AssetController;
use App\Http\Controllers\PageBuilder\AssetLookupController;
use App\Http\Controllers\PageBuilder\EditorController;
use App\Support\Admin\AdminLanguage;
use App\PageBuilder\Services\PageWorkflow;
use App\PageBuilder\Support\TemplateCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$prefix = trim((string) config('page_builder.prefix', 'pagebuilder'), '/');

Route::middleware(config('page_builder.middleware', []))
    ->prefix($prefix)
    ->name('pagebuilder.')
    ->group(function (): void {
        Route::get('/', fn () => redirect()->route('pagebuilder.editor', ['slug' => 'home']))->name('dashboard');
        Route::get('/editor/{slug?}', EditorController::class)->where('slug', '.*')->name('editor');
        Route::get('/preview/{slug?}', function (Request $request, ?string $slug = null) {
            $language = AdminLanguage::selected($request);
            $value = trim(strtolower((string) $slug), '/');
            $pageKey = match ($value) {
                '', 'home', 'index' => 'index',
                'header', 'site-header' => '__header',
                'footer', 'site-footer' => '__footer',
                default => preg_replace('/[^a-z0-9_-]/', '', $value) ?: 'index',
            };
            $path = match ($pageKey) {
                '__header', '__footer', 'index' => '/',
                'about' => '/about',
                'contact' => '/contact',
                'certificates' => '/certificates',
                'gallery' => '/gallery',
                'trainings' => '/trainings',
                'articles', 'article_detail' => '/articles',
                'profile' => '/profile',
                default => '/page?key='.urlencode($pageKey),
            };

            return redirect()->to(url($path).(str_contains($path, '?') ? '&' : '?').http_build_query([
                'lang' => $language,
                'pb_preview' => 1,
                'pb_page' => $pageKey,
            ]));
        })->where('slug', '.*')->name('preview');
        Route::get('/canvas/{slug?}', function (?string $slug = null) {
            return view('generic-pagebuilder.canvas', ['slug' => trim((string) $slug, '/') ?: 'home']);
        })->where('slug', '.*')->name('canvas');

        Route::get('/api/pages', [ApiController::class, 'index'])->name('api.pages');
        Route::get('/api/pages/{slug}/history', [ApiController::class, 'history'])->where('slug', '.*')->name('api.history');
        Route::post('/api/pages/{slug}/publish', [ApiController::class, 'publish'])->where('slug', '.*')->name('api.publish');
        Route::post('/api/pages/{slug}/rollback', [ApiController::class, 'rollback'])->where('slug', '.*')->name('api.rollback');
        Route::post('/api/pages/{slug}/restore', [ApiController::class, 'restore'])->where('slug', '.*')->name('api.restore');
        Route::patch('/api/pages/{slug}/archive', [ApiController::class, 'archive'])->where('slug', '.*')->name('api.archive');
        Route::delete('/api/pages/{slug}', [ApiController::class, 'destroy'])->where('slug', '.*')->name('api.destroy');
        Route::put('/api/pages/{slug}', [ApiController::class, 'save'])->where('slug', '.*')->name('api.save');
        Route::get('/api/pages/{slug}', [ApiController::class, 'bootstrap'])->where('slug', '.*')->name('api.bootstrap');

        Route::get('/api/assets', [AssetController::class, 'index'])->name('api.assets');
        Route::post('/api/assets', [AssetController::class, 'store'])->name('api.assets.store');
        Route::get('/api/assets/{asset}', AssetLookupController::class)->name('api.assets.show');
        Route::patch('/api/assets/{asset}', [AssetController::class, 'update'])->name('api.assets.update');
        Route::delete('/api/assets/{asset}', [AssetController::class, 'destroy'])->name('api.assets.destroy');
    });

$publicPrefix = trim((string) config('page_builder.public_prefix', 'pagebuilder-site'), '/');

Route::get($publicPrefix.'/{slug?}', function (?string $slug = null) {
    $value = trim((string) $slug, '/') ?: 'home';
    $result = app(PageWorkflow::class)->published($value);

    abort_if($result === null, 404);

    return view(app(TemplateCatalog::class)->view($result['page']->template), $result);
})->where('slug', '.*')->name('pagebuilder.public');
