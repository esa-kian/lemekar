<?php

namespace App\Console;

use App\Console\Commands\TaskReminderCron;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    
    protected $commands = [
        TaskReminderCron::class,
        //
    ];

  
    protected function schedule(Schedule $schedule)
    {
      
        $schedule->command('task:cron')
            ->everyMinute();
    }

 
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
