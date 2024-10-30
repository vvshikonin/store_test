<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\CRM_API_client;
use App\Jobs\GenerateXMLJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\LowStockNotification;
use Illuminate\Support\Facades\Log;
use App\Models\V1\Transactionable;
use App\Mail\TransactionBrandsNotification;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\GenerateXMLCatalog::class,
        Commands\UpdateExpenseTypeId::class,
        Commands\UpdateExpenseTransactions::class,
        Commands\UpdateMoneyRefundsCommand::class,
        Commands\UpdateMoneyRefundTransactions::class,
        Commands\UpdateExpensesAccountingPeriod::class,
        Commands\CheckOldProductsForSale::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call(function () {
        //     if (now()->isWeekend()) {
        //         return;
        //     }

        //     // Получаем бренды с транзакциями за последние сутки или трое суток
        //     $days = now()->isMonday() ? 3 : 1;
        //     $brands = Transactionable::getBrandsWithTransactionsForLastDays($days);

        //     Mail::to('taskstoit@yandex.ru')->send(new TransactionBrandsNotification($brands));
        // })->dailyAt('07:00');

        // $schedule->job(new GenerateXMLJob)->dailyAt('06:50');

        $schedule->command('run:generate-xml-catalog')->dailyAt('07:00');
        // $schedule->command('products:check-old-for-sale')->dailyAt('07:00');

        $schedule->command('queue:retry all')->hourly();
        $schedule->command('queue:work --stop-when-empty')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
