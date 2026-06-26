<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentModuleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LegacyRedirectController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\SearchController;
use App\Http\Controllers\Public\SiteAuthController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\Public\TrainingController;
use App\Http\Middleware\EnsureAdminAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', [PageController::class, 'about'])->name('pages.about');
Route::get('/page', [PageController::class, 'dynamic'])->name('pages.dynamic');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/certificates', [PageController::class, 'certificates'])->name('certificates.index');
Route::get('/gallery', GalleryController::class)->name('gallery.index');
Route::get('/trainings', TrainingController::class)->name('trainings.index');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/article', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/profile', ProfileController::class)->name('profile');
Route::get('/search', SearchController::class)->name('search.api');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::prefix('{lang}')->where(['lang' => 'az|en|ru|tr'])->group(function (): void {
    Route::get('/', HomeController::class)->name('localized.home');
    Route::get('/about', [PageController::class, 'about'])->name('localized.about');
    Route::get('/contact', [PageController::class, 'contact'])->name('localized.contact');
    Route::get('/certificates', [PageController::class, 'certificates'])->name('localized.certificates');
    Route::get('/gallery', GalleryController::class)->name('localized.gallery');
    Route::get('/trainings', TrainingController::class)->name('localized.trainings');
    Route::get('/articles', [ArticleController::class, 'index'])->name('localized.articles');
    Route::get('/articles/{slug}', [ArticleController::class, 'show'])->where('slug', '[A-Za-z0-9_-]+')->name('localized.article');
    Route::get('/pages/{key}', [PageController::class, 'dynamic'])->where('key', '[A-Za-z0-9_-]+')->name('localized.page');
});

Route::get('/uploads/certificates/with-qr/{name}.svg', function (string $name) {
    $pdfPath = 'uploads/certificates/with-qr/'.$name.'.pdf';
    abort_unless(is_file(public_path($pdfPath)), 404);
    return redirect(asset($pdfPath), 301);
})->where('name', '[A-Za-z0-9._-]+');

Route::get('/login', [SiteAuthController::class, 'showLogin'])->name('site.login.page');
Route::get('/register', [SiteAuthController::class, 'showRegister'])->name('site.register.page');
Route::post('/auth/login', [SiteAuthController::class, 'login'])->middleware('throttle:5,1')->name('site.login');
Route::post('/auth/register', [SiteAuthController::class, 'register'])->middleware('throttle:3,1')->name('site.register');
Route::get('/auth/google', [SiteAuthController::class, 'googleLogin'])->name('site.google.login');
Route::get('/auth/google/callback', [SiteAuthController::class, 'googleCallback'])->name('site.google.callback');
Route::post('/auth/google/token', [SiteAuthController::class, 'googleTokenLogin'])->name('site.google.token');
Route::get('/logout', [SiteAuthController::class, 'logout'])->name('site.logout');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'show'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');

    Route::middleware(EnsureAdminAuthenticated::class)->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/dashboard', DashboardController::class)->name('dashboard.view');
        Route::get('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::post('/language', [AdminLanguageController::class, 'switch'])->name('language.switch');
        Route::get('/settings', [SettingsController::class, 'index'])->middleware('admin.role:super_admin')->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->middleware('admin.role:super_admin')->name('settings.update');
        Route::get('/users', [UserController::class, 'index'])->middleware('admin.role:super_admin')->name('users.index');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggle'])->middleware('admin.role:super_admin')->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('admin.role:super_admin')->name('users.destroy');
        Route::get('/contact-messages', [ContactMessageController::class, 'index'])->name('contact.index');
        Route::post('/contact-messages/{message}/read', [ContactMessageController::class, 'read'])->name('contact.read');
        Route::delete('/contact-messages/{message}', [ContactMessageController::class, 'destroy'])->name('contact.destroy');
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/media', [MediaController::class, 'index'])->middleware('admin.role:super_admin,designer,editor')->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->middleware('admin.role:super_admin,designer,editor')->name('media.store');
        Route::delete('/media', [MediaController::class, 'destroy'])->middleware('admin.role:super_admin,designer')->name('media.destroy');

        require __DIR__.'/admin-page-builder-react.php';

        Route::post('/articles/{article}/notify', [ContentModuleController::class, 'notifyArticle'])->middleware('admin.role:super_admin,publisher')->name('articles.notify');
        Route::get('/{module}', [ContentModuleController::class, 'index'])->middleware('admin.role:super_admin,designer,editor,publisher')->where('module', '[A-Za-z0-9_-]+')->name('modules.index');
        Route::post('/{module}/sort', [ContentModuleController::class, 'sort'])->middleware('admin.role:super_admin,editor,publisher')->where('module', '[A-Za-z0-9_-]+')->name('modules.sort');
        Route::post('/{module}', [ContentModuleController::class, 'store'])->middleware('admin.role:super_admin,editor,publisher')->where('module', '[A-Za-z0-9_-]+')->name('modules.store');
        Route::delete('/{module}/{id}', [ContentModuleController::class, 'destroy'])->middleware('admin.role:super_admin,publisher')->where('module', '[A-Za-z0-9_-]+')->name('modules.destroy');
    });
});

require __DIR__.'/generic-pagebuilder.php';

Route::any('/{any}', [LegacyRedirectController::class, 'redirect'])->where('any', '.*');
