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
        // HONEY TASKS
        // prefer to run it once by run cron job on the server
        // $schedule->command('honey:product-db-init')->monthly();

        // connect to honey and update product qty on DB
        // $schedule->command('honey:connect-honey')->everyTwoHours();

        // update qty in DB and store with the qty from honey
        $schedule->command('honey:sync-honey-db-shopify')->everyTwoHours();
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
