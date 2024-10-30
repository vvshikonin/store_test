<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FillFieldsInInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $invoices = DB::table('invoices')->get();

            foreach ($invoices as $invoice) {
                // Ищем соответствующие записи в других таблицах
                $legal_entity = DB::table('legal_entities')
                                    ->where('name', $invoice->legal_entity)
                                    ->first();

                $payment_method = DB::table('payment_methods')
                                    ->where('name', $invoice->cashless_payment_type)
                                    ->first();

                DB::table('invoices')
                    ->where('id', $invoice->id)
                    ->update([
                        'legal_entity_id' => $legal_entity->id ?? null,
                        'payment_method_id' => $payment_method->id ?? null,
                    ]);
            };
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
}
