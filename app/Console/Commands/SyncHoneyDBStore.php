<?php

namespace App\Console\Commands;

use App\Http\Controllers\HoneyController;
use Illuminate\Console\Command;

class SyncHoneyDBStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'honey:sync-honey-db-shopify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to honey and update product qty in DB and Shopify store';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HoneyController::class);
        $controller->syncQtyHoneyDbStore();
        info('store, db product qty updated with honey qty');
    }
}
