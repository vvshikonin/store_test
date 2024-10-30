<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\V1\Order;
use App\Models\V1\Defect;

class MigrateDefectOrdersToDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $defectiveOrders = Order::where('is_defect', 1)->get();

        foreach ($defectiveOrders as $order) {
            Defect::create([
                'order_id' => $order->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('defects', function (Blueprint $table) {
            //
        });
    }
}
