<?php

namespace App\Console\Commands;

use App\Services\Entities\MoneyRefundService;
use Illuminate\Console\Command;

class UpdateMoneyRefundsCommand extends Command
{
    protected $signature = 'money-refunds:update-transactions';
    protected $description = 'Обновляет все выполненные возвраты ДС';

    private $moneyRefundService;

    public function __construct(MoneyRefundService $moneyRefundService)
    {
        parent::__construct();
        $this->moneyRefundService = $moneyRefundService;
    }

    public function handle(): int
    {
        $this->info('Начало обновления всех выполненных возвратов ДС...');

        $refunds = $this->moneyRefundService->getAllCompletedRefunds();

        foreach ($refunds as $refund) {
            $this->info('Идёт обновление возврата ДС: ' . $refund->id);

            $completedAt = $refund->completed_at;

            $this->moneyRefundService->markAsIncomplete($refund);
            $this->moneyRefundService->markAsCompleted($refund, $completedAt);
            $this->info('Обновлен возврат ДС: ' . $refund->id);
        }

        $this->info('Обновление всех выполненных возвратов ДС завершено.');

        return 0;
    }
}
