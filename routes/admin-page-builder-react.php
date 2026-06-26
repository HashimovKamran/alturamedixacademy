<?php

use App\Http\Controllers\Admin\PageBuilderApiController;
use App\Http\Controllers\Admin\PageBuilderAssetController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Middleware\EnsureVisualBuilderPayloadSize;
use Illuminate\Support\Facades\Route;

Route::get('/page-editor', [PageBuilderController::class, 'index'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('page-builder.index');
Route::get('/page-editor/canvas', [PageBuilderController::class, 'canvas'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('page-builder.canvas');
Route::redirect('/live-editor', '/admin/page-editor')->name('visual-editor.index');

Route::prefix('/page-editor/api')->name('page-builder.api.')->group(function (): void {
    Route::get('/bootstrap', [PageBuilderApiController::class, 'bootstrap'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('bootstrap');
    Route::get('/pages', [PageBuilderApiController::class, 'pages'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('pages');
    Route::get('/history', [PageBuilderApiController::class, 'history'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('history');
    Route::post('/draft', [PageBuilderApiController::class, 'save'])
        ->middleware([EnsureVisualBuilderPayloadSize::class, 'admin.role:super_admin,designer,editor'])
        ->name('draft');
    Route::post('/publish', [PageBuilderApiController::class, 'publish'])->middleware('admin.role:super_admin,publisher')->name('publish');
    Route::post('/rollback', [PageBuilderApiController::class, 'rollback'])->middleware('admin.role:super_admin,publisher')->name('rollback');
    Route::post('/archive', [PageBuilderApiController::class, 'archive'])->middleware('admin.role:super_admin,publisher')->name('archive');
    Route::post('/restore', [PageBuilderApiController::class, 'restore'])->middleware('admin.role:super_admin,publisher')->name('restore');
    Route::post('/remove', [PageBuilderApiController::class, 'remove'])->middleware('admin.role:super_admin')->name('remove');
    Route::get('/assets', [PageBuilderAssetController::class, 'index'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('assets.index');
    Route::get('/assets/{asset}', [PageBuilderAssetController::class, 'show'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('assets.show');
    Route::post('/assets', [PageBuilderAssetController::class, 'store'])->middleware('admin.role:super_admin,designer,editor')->name('assets.store');
    Route::patch('/assets/{asset}', [PageBuilderAssetController::class, 'update'])->middleware('admin.role:super_admin,designer,editor')->name('assets.update');
    Route::post('/assets/{asset}/remove', [PageBuilderAssetController::class, 'destroy'])->middleware('admin.role:super_admin,designer')->name('assets.remove');
});
