<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\Stock;
use App\Models\V1\InvoiceProduct;

class AddLastReceiveDateToStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->date('last_receive_date')->nullable()->default(NULL)->after('user_comment');
        });

        $stocks = Stock::all();
        foreach ($stocks as $stock) {
            $lastTransaction = $stock->transactions()
                ->where('type', 'In') // Убедитесь, что тип транзакции "IN"
                ->where('transactionable_type', InvoiceProduct::class) // Убедитесь, что transactionable_type соответствует InvoicePosition
                ->latest()
                ->first();

            if ($lastTransaction) {
                $stock->last_receive_date = $lastTransaction->created_at; // Устанавливаем last_receive_date на дату создания последней транзакции
                $stock->save(); // Сохраняем изменения
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropColumn('last_receive_date');
        });
    }
}
