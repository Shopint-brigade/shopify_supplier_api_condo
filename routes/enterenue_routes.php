<?php

use App\Http\Controllers\EnterenueController;
use App\Http\Controllers\EnterenueDashboardController;

Route::prefix('/admin/enterenue')->group(function() {
    Route::get('/search', [EnterenueDashboardController::class, 'enterenueSearchForm'])->name('admin.enterenue.search.form');
    Route::post('/serach',[EnterenueDashboardController::class, 'enterenueSearch'] )->name('admin.enterenue.search');
    Route::get('/push/{upc}', [EnterenueDashboardController::class, 'enterenuePushProduct'])->name('admin.enterenue.pushProduct');
    Route::get('/products', [EnterenueDashboardController::class, 'displayPushedProducts'])->name('admin.enterenue.products');
    Route::delete('/products/{product}', [EnterenueDashboardController::class, 'destroy'])->name('admin.enterenue.products.destory');
});

 // cron job routes
 Route::get("/ent-products", [EnterenueController::class, 'saveProductsinDB']);
 Route::get("/ent-sync", [EnterenueController::class, 'syncProductPriceAndStock']);