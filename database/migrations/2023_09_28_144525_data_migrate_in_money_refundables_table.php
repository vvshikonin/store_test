<?php

use App\Models\V1\MoneyRefundable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\V1\Invoice;
use App\Models\V1\Defect;
use App\Models\V1\ProductRefund;
use App\Models\V1\ContractorRefund;

class DataMigrateInMoneyRefundablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            $moneyRefundables = MoneyRefundable::all();
            foreach ($moneyRefundables as $moneyRefundable) {
                $refundableId = $moneyRefundable->refundable_id;
                $refundableType = $moneyRefundable->refundable_type;

                $moneyRefundable->refund_sum_money = $moneyRefundable->actual_refund ? $moneyRefundable->actual_refund : 0;

                switch ($refundableType) {
                    case Invoice::class:
                        $invoice = Invoice::find($refundableId);
                        $moneyRefundable->debt_sum = $invoice->refusedSum;
                        $moneyRefundable->contractor_id = $invoice->contractor_id;
                        $moneyRefundable->payment_method_id = $invoice->payment_method_id;
                        $moneyRefundable->legal_entity_id = $invoice->legal_entity_id;
                        break;

                    case Defect::class:
                        $defect = Defect::find($refundableId);
                        $order = $defect->order;
                        $moneyRefundable->debt_sum = $order->orderProducts->sum(function ($product) {
                            return $product->amount * $product->avg_price;
                        });
                        $moneyRefundable->contractor_id = $order->orderProducts()->first()->contractor_id;
                        $moneyRefundable->payment_method_id = $defect->payment_method_id;
                        $moneyRefundable->legal_entity_id = $defect->legal_entity_id;
                        break;
                    case ProductRefund::class:
                        $productRefund = ProductRefund::find($refundableId);
                        $order = $productRefund?->order;
                        if ($order) {
                            $moneyRefundable->debt_sum = $order->orderProducts->sum(function ($product) {
                                return $product->amount * $product->avg_price;
                            });
                            $moneyRefundable->contractor_id = $order->orderProducts()->first()->contractor_id;
                            $moneyRefundable->payment_method_id = $productRefund->payment_method_id;
                            $moneyRefundable->legal_entity_id = $productRefund->legal_entity_id;
                        }

                        break;
                    case ContractorRefund::class:
                        $contractorRefund = ContractorRefund::find($refundableId);
                        $invoice = $contractorRefund->invoice;
                        $moneyRefundable->debt_sum = $contractorRefund->contractorRefundProducts->sum(function ($product) {
                            return $product->amount * $product->invoiceProduct->price;
                        });
                        $moneyRefundable->contractor_id = $invoice->contractor_id;
                        $moneyRefundable->payment_method_id = $invoice->payment_method_id;
                        $moneyRefundable->legal_entity_id = $invoice->legal_entity_id;
                        break;
                    case MoneyRefundable::class:
                        $moneyRefundable->debt_sum = $moneyRefundable->sum;
                        break;
                }

                $moneyRefundable->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
