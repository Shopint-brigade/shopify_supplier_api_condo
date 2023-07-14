<?php

namespace App\Console\Commands;

use App\Http\Controllers\EnterenueController;
use Illuminate\Console\Command;

class EnterenueShopifySyncStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterenue:shopify-sync-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product stock(qty) DB -> Shopify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EnterenueController::class);
        $controller->syncProductStockWithShopify();
    }
}
