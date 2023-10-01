<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        ////////////////
        // HONEY 
        ///////////////
        // update qty in DB and store with the qty from honey
        $schedule->command('honey:sync-honey-db-shopify')->everyMinute();
        $schedule->command('honey:product-db-init')->everyMinute();

        ////////////////
        // Entrenue 
        ///////////////
        //get all products from Enternue shopify store and save in DB
        $schedule->command('enterenue:shopify-init-products')->everySixHours();
        // update DB products qty and price with data from Enternue
        $schedule->command('enterenue:update-db-products')->everyTwoHours();
        // update(sync) product qty on shopify
        $schedule->command('enterenue:shopify-sync-stock')->everyThirtyMinutes();
        // update(sync) product price on shopify
        $schedule->command('enterenue:shopify-sync-price')->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
