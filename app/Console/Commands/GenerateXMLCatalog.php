<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\GenerateXMLJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\TransactionBrandsNotification;
use App\Models\V1\Transactionable;
use Illuminate\Support\Facades\Log;

class GenerateXMLCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:generate-xml-catalog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает задачи для генерации каталогов брендов с акционными товарами';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Выполняем GenerateXMLJob...');
        dispatch(new GenerateXMLJob());

        $this->info('Выполняем TransactionBrandsNotification...');

        // Проверяем, является ли текущий день выходным
        if (!now()->isWeekend()) {
            $days = now()->isMonday() ? 3 : 1;
            $xmlCatalogs = Transactionable::getBrandsWithTransactionsForLastDays($days);

            try {
                Mail::to('taskstoit@yandex.ru')->send(new TransactionBrandsNotification($xmlCatalogs));
            } catch (\Exception $e) {
                Log::error('Ошибка при отправке email: ' . $e->getMessage());
            }
        } else {
            $this->info('Сегодня выходной. Пропускаем TransactionBrandsNotification.');
        }

        $this->info('Задачи по генерации акционных каталогов выполнены.');

        return 0;
    }
}
