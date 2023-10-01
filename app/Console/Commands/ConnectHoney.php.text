<?php

namespace App\Console\Commands;

use App\Http\Controllers\HoneyController;
use Illuminate\Console\Command;

class ConnectHoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'honey:connect-honey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Connect to honey and update product qty in DB';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = app(HoneyController::class);
        $controller->connectHoney();
        info('db qty updated by honey');
    }
}
