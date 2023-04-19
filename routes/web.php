<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FronEndController;
use App\Http\Controllers\HoneyController;
use Illuminate\Support\Facades\Route;


Route::get("/", [FronEndController::class, "index"]);
// get shopify location id by name
Route::get("/location", [HoneyController::class, 'getShopifyLocationByName']);
// get shopify collection products
Route::get("/products", [HoneyController::class, 'getShopifyProducts']);
// connect to honey place
Route::get("/connect", [HoneyController::class, 'connectHoney']);
// sync aty honey_place <=> DB <=> Store
Route::get("/sync", [HoneyController::class, 'syncQtyHoneyDbStore']);
//  Update the product qty on shopify store
Route::get("/update-qty", [HoneyController::class, 'updateQtyOnShopifyStore']);
// scrap images from honey_place
Route::get("/scrap", [HoneyController::class, 'scrapProductImages']);

// Laravel Auth routes
Auth::routes();

// admin routes
Route::get('/admin', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get("/admin/logs", [DashboardController::class, 'logs'])->name('admin.logs');
Route::get("/admin/new-products", [DashboardController::class, 'newProducts'])->name('admin.new.products');


//  Shopify webhooks
Route::post('/webhook-honey/newproduct', [HoneyController::class, 'receiveShopifyWebhookNewProduct']);
