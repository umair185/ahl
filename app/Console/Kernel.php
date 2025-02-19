<?php

namespace App\Console;

use App\Models\ScanOrder;
use App\Models\Order;
use Carbon;
use Log;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
         $schedule->call(function() {
            Log::info('Callen Every Minute');
            $check_order = ScanOrder::where('middle_man_scan_date', '>', \Carbon\Carbon::now()->subDays(2))->get();
            foreach($check_order as $check)
            {
                $time_check = round((strtotime(date("Y/m/d H:i:s")) - strtotime($check->middle_man_scan_date))/60);
                if($time_check > 1400)
                {
                    $order = Order::where('id', $check->order_id)->where('delayed_status', 0)->update(['delayed_status' => 1]);
                }
            }
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
