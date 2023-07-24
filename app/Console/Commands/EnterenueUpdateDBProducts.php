<?php

namespace App\Console\Commands;

use App\Http\Controllers\EnterenueController;
use Illuminate\Console\Command;

class EnterenueUpdateDBProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterenue:update-db-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all products qty and price on DB with data from Enternue site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(EnterenueController::class);
        $controller->syncQtyAndPriceEnternueDB();
        info('db products price and qty updated from Enternue site.');

    }
}
