<?php

use App\Http\Controllers\Admin\PageBuilderApiController;
use App\Http\Controllers\Admin\PageBuilderController;
use Illuminate\Support\Facades\Route;

Route::get('/page-editor', [PageBuilderController::class, 'index'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('page-builder.index');
Route::get('/page-editor/canvas', [PageBuilderController::class, 'canvas'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('page-builder.canvas');
Route::prefix('/page-editor/api')->name('page-builder.api.')->group(function (): void {
    Route::get('/bootstrap', [PageBuilderApiController::class, 'bootstrap'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('bootstrap');
    Route::get('/pages', [PageBuilderApiController::class, 'pages'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('pages');
    Route::get('/history', [PageBuilderApiController::class, 'history'])->middleware('admin.role:super_admin,designer,editor,publisher')->name('history');
    Route::post('/draft', [PageBuilderApiController::class, 'save'])->middleware('admin.role:super_admin,designer,editor')->name('draft');
    Route::post('/publish', [PageBuilderApiController::class, 'publish'])->middleware('admin.role:super_admin,publisher')->name('publish');
    Route::post('/rollback', [PageBuilderApiController::class, 'rollback'])->middleware('admin.role:super_admin,publisher')->name('rollback');
});
