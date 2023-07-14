<?php

use App\Http\Controllers\EnterenueController;
use App\Http\Controllers\EnterenueDashboardController;

Route::prefix('/admin/enterenue')->group(function() {
    Route::get('/search', [EnterenueDashboardController::class, 'enterenueSearchForm'])->name('admin.enterenue.search.form');
    Route::post('/serach',[EnterenueDashboardController::class, 'enterenueSearch'] )->name('admin.enterenue.search');
    Route::get('/push/{upc}', [EnterenueDashboardController::class, 'enterenuePushProduct'])->name('admin.enterenue.pushProduct');
    Route::get('/synced-products', [EnterenueDashboardController::class, 'displayPushedProducts'])->name('admin.enterenue.synced.products');
    Route::delete('/products/{product}', [EnterenueDashboardController::class, 'destroy'])->name('admin.enterenue.products.destory');
    Route::get('/products', [EnterenueDashboardController::class, 'showAllShopifyProducts'])->name('admin.enterenue.shopify.products');
    Route::get('/logs', [EnterenueDashboardController::class, 'logs'])->name('admin.enterenue.logs');
});

 // cron job routes
 Route::get("/ent-products", [EnterenueController::class, 'saveProductsinDB']);
 Route::get("/sync-qty-price-db", [EnterenueController::class, 'syncQtyAndPriceEnternueDB']);
 Route::get("/ent-sync-qty", [EnterenueController::class, 'syncProductStockWithShopify']);
 Route::get("/ent-sync-price", [EnterenueController::class, 'syncProductPricekWithShopify']);
