<?php

namespace App\Console\Commands;

use App\Http\Controllers\EnterenueController;
use Illuminate\Console\Command;

class EnterenueShopifySyncPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterenue:shopify-sync-price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product Price DB -> Shopify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EnterenueController::class);
        $controller->syncProductPricekWithShopify();
    }
}
