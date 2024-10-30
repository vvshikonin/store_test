<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\MoneyRefundable;
use App\Services\Entities\MoneyRefundService;
use App\Services\Transactions\Cash\MoneyRefundCashTransaction;

class UpdateMoneyRefundTransactions extends Command
{
    // Название и описание команды
    protected $signature = 'money-refund:update-transactions';
    protected $description = 'Обновляет все транзакции у Incomes Money Refundable';

    // protected $moneyRefundService;
    protected $cashTransaction;

    public function __construct(MoneyRefundService $moneyRefundService)
    {
        parent::__construct();
        // $this->moneyRefundService = $moneyRefundService;
        $this->cashTransaction = new MoneyRefundCashTransaction();
    }

    public function handle()
    {
        // Получаем все записи MoneyRefundable
        $moneyRefunds = MoneyRefundable::with('incomes')->get();

        foreach ($moneyRefunds as $moneyRefund) {
            $this->info("Обновление транзакций для MoneyRefundable ID: {$moneyRefund->id}");

            if ($moneyRefund->status == 1) {
                // Обновляем транзакции для каждого Income
                foreach ($moneyRefund->incomes as $income) {
                    $this->cashTransaction->makeReplaceOutcomingTransactions(
                        $income,
                        $income->sum,
                        $income->payment_method_id,
                        $income->date
                    );
                }
            }
        }

        $this->info('Все транзакции успешно обновлены.');
    }
}
