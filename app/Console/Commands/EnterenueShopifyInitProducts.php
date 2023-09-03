<?php

namespace App\Console\Commands;

use App\Http\Controllers\EnterenueController;
use Illuminate\Console\Command;

class EnterenueShopifyInitProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterenue:shopify-init-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all product from shopify store and save in DB(price and qty are null)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EnterenueController::class);
        $controller->saveProductsinDB();
        info('all products from shopify saved in DB.');

    }
}
