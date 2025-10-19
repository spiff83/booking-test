<?php

use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\BookingController;

Route::get('/', [ServiceController::class, 'index'])->name('home');

Route::get('/services/{service}', [ServiceController::class,'show'])->name('services.show');
Route::get('/services/{service}/slots', [SlotController::class,'serviceSlots'])->name('slots.service');
Route::post('/bookings', [BookingController::class,'store'])->name('bookings.store');
Route::get('/test-csrf', fn() => view('test-csrf'));


Route::prefix('admin')->group(function () {
    Route::get('/', [\App\Http\Controllers\AdminController::class, 'index'])->name('admin.index');
    Route::get('/services/create', [\App\Http\Controllers\AdminController::class, 'createService'])->name('admin.services.create');
    Route::post('/services', [\App\Http\Controllers\AdminController::class, 'storeService'])->name('admin.services.store');
    Route::get('/services/{id}/edit', [\App\Http\Controllers\AdminController::class, 'editService'])->name('admin.services.edit');
    Route::post('/services/{id}', [\App\Http\Controllers\AdminController::class, 'updateService'])->name('admin.services.update');
    Route::post('/services/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteService'])->name('admin.services.delete');

    Route::post('/service/{id}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleService'])->name('admin.toggle.service');
    Route::post('/services/{serviceId}/variants', [\App\Http\Controllers\AdminController::class, 'storeVariant'])->name('admin.variants.store');
    Route::post('/variants/{id}/delete', [\App\Http\Controllers\AdminController::class, 'deleteVariant'])->name('admin.variants.delete');
    Route::post('/variant/{id}/toggle', [\App\Http\Controllers\AdminController::class, 'toggleVariant'])->name('admin.toggle.variant');

    Route::get('/services/{id}/schedule', [\App\Http\Controllers\AdminController::class, 'editSchedule'])->name('admin.services.schedule');
    Route::post('/services/{id}/schedule', [\App\Http\Controllers\AdminController::class, 'updateSchedule'])->name('admin.services.schedule.update');

    Route::get('/services/{id}/history', [\App\Http\Controllers\AdminController::class, 'history'])->name('admin.services.history');
	Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
});
