<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FronEndController;
use App\Http\Controllers\HoneyController;
use Illuminate\Support\Facades\Route;

// get shopify location id by name
// Route::get("/location", [HoneyController::class, 'getShopifyLocationByName']);
// get shopify collection products
// Route::get("/products", [HoneyController::class, 'getShopifyProducts']);
// connect to honey place
// Route::get("/connect", [HoneyController::class, 'connectHoney']);
// Update the product qty on shopify store
// Route::get("/update-qty", [HoneyController::class, 'updateQtyOnShopifyStore']);
// scrap images from honey_place
// Route::get("/scrap", [HoneyController::class, 'scrapProductImages']);

Route::get("/", [FronEndController::class, "index"]);
// sync aty honey_place <=> DB <=> Store
Route::get("/sync", [HoneyController::class, 'syncQtyHoneyDbStore']);


// Laravel Auth routes
Auth::routes();

// admin routes
// main page
Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// logs
Route::get("/admin/logs", [DashboardController::class, 'logs'])->name('admin.logs');
// display products added by shopify webhook
Route::get("/admin/new-products", [DashboardController::class, 'newProducts'])->name('admin.new.products');
// sync product image between honey place and shopify store
Route::get('/admin/sync-images', [DashboardController::class, 'syncImages'])->name('admin.sync.images');
Route::get('/admin/product/search', [DashboardController::class, 'searchProducts'])->name('admin.product.search');
Route::post('/admin/sync-images', [DashboardController::class, 'postSyncImages'])->name('admin.syn.images.post');
// display products (images synced)
Route::get('/admin/synced-products-images', [DashboardController::class, 'displaySyncedProducts'])->name('admin.list.syned.products');
//  Shopify webhooks
Route::post('/webhook-honey/newproduct', [HoneyController::class, 'receiveShopifyWebhookNewProduct']);


