<?php
use App\Http\Controllers\EnterenueDashboardController;

Route::prefix('/admin/enterenue')->group(function() {
    Route::get('/search', [EnterenueDashboardController::class, 'enterenueSearchForm'])->name('admin.enterenue.search.form');
    Route::post('/serach',[EnterenueDashboardController::class, 'enterenueSearch'] )->name('admin.enterenue.search');
    Route::get('/push/{upc}', [EnterenueDashboardController::class, 'enterenuePushProduct'])->name('admin.enterenue.pushProduct');
});