<?php
namespace App\Services\Transactions;

use Illuminate\Support\Facades\DB;

class AbstractTransactionService
{
 /**
     * Тип транзакции поступления.
     */
    public const IN = 'In';

    /**
     * Тип транзакции списания.
     */
    public const OUT = 'Out';

    /**
     * Экземпляр PDO с активной транзакцией БД.
     * @var PDO|null null если транзакция не активна.
     */
    protected $DBTransaction = null;

    /**
     * Контейнер для выполнения транзакций. Обеспечивает фиксацию изменений в БД в случае успеха и откат в случае исключений.
     *
     * @param callback $callback
     */
    public function transactionsContainer($callback)
    {
        $this->startDBTransactions();
        try {
            $callback();
            $this->commitDBTransactions();
        } catch (\Exception $e) {
            $this->rollbackDBTransactions();
            throw $e;
        }
    }

    /**
     * Начинает транзакцию БД.
     */
    private function startDBTransactions()
    {
        // if ($this->DBTransaction == null) {
        //     $this->DBTransaction = DB::connection()->getPdo();
        //     $this->DBTransaction->beginTransaction();
        // }
    }

    /**
     * Фиксирует транзакцию БД.
     */
    private function commitDBTransactions()
    {
        // if ($this->DBTransaction !== null) {
        //     $this->DBTransaction->commit();
        //     $this->DBTransaction = null;
        // }
    }

    /**
     * Производит откат транзакции БД.
     */
    public function rollbackDBTransactions()
    {
        // if ($this->DBTransaction !== null) {
        //     $this->DBTransaction->rollBack();
        //     $this->DBTransaction = null;
        // }
    }

}
