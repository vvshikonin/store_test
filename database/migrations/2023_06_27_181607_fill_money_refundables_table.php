<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\MoneyRefundable;

class FillMoneyRefundablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('money_refundables', function (Blueprint $table) {
            $oldMoneyRefunds = DB::table('money_refunds')->get();
            foreach($oldMoneyRefunds as $refund) {

                $newRefund = new MoneyRefundable;

                if ($refund->invoice_id !== null) 
                {
                    $newRefund->refundable_id = $refund->invoice_id;
                    $newRefund->refundable_type = 'App\Models\V1\Invoice';
                } else {
                    $oldRefund = DB::table('orders')->find($refund->order_id);
                    $currentEntity = $oldRefund->is_defect ? DB::table('defects')->where('defects.order_id', $refund->order_id)->first() : DB::table('product_refunds')->where('product_refunds.order_id', $refund->order_id)->first(); 
                    $newRefund->refundable_id = $currentEntity->id;
                    $newRefund->refundable_type = $oldRefund->is_defect ? 'App\Models\V1\Defect' : 'App\Models\V1\ProductRefund'; 
                }

                $newRefund->status = $refund->status;
                $newRefund->created_at = $refund->created_at;
                $newRefund->updated_at = $refund->updated_at;

                $newRefund->save();
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
        //
    }
}