<?php

namespace App\Console\Commands;

use App\Http\Controllers\HoneyController;
use App\Models\Honey;
use Illuminate\Console\Command;

class ProductDbInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'honey:product-db-init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $honeyController = app(HoneyController::class);
        $data = Honey::all();
        if(count($data) == 0 ) {
            $honeyController->getShopifyProducts();
        }
        info('products saved in DB');
    }
}
