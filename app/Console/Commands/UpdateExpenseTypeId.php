<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\V1\Expenses\Expense;
use Illuminate\Support\Facades\DB;

class UpdateExpenseTypeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:expense_type_id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $expenses = Expense::all();
        $userId = 1;

        foreach ($expenses as $expense) {
            $expenseItem = $expense->items()->first();

            if ($expenseItem) {
                DB::table('expenses')
                    ->where('id', $expense->id)
                    ->update([
                        'expense_type_id' => $expenseItem->expense_type_id,
                        'updated_by' => $userId
                    ]);
            }
        }
    }
}
