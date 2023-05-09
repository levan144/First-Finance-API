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
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

    $this->load(__DIR__.'/../app/Console/Commands');

        

        require base_path('routes/console.php');
        $this->registerCommand('App\Console\Commands\UpdateExchangeRates');
    }

    /**
     * Register a console command with the application.
     *
     * @param  string  $command
     * @return void
     */
   public function registerCommand($command)
{
    $this->app->singleton($command, function () use ($command) {
        return new $command();
    });
}
}
