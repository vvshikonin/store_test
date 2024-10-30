<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->integer('amount')->nullable()->default(0);
            $table->string('external_id')->nullable();
            $table->float('avg_price')->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('contractor_id')->nullable();
        });

        $orderPositions = DB::table('order_positions')->select(
            'order_id',
            'amount',
            'purchase_price',
            'crm_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'product_id',
            'contractor_id'
            )
            ->get();

        foreach ($orderPositions as $position) {
            DB::table('order_products')->insert([
                'order_id' => $position->order_id,
                'amount' => $position->amount,
                'external_id' => $position->crm_id,
                'avg_price' => $position->purchase_price,
                'created_at' => $position->created_at,
                'updated_at' => $position->updated_at,
                'deleted_at' => $position->deleted_at,
                'product_id' => $position->product_id,
                'contractor_id' => $position->contractor_id,
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
        Schema::dropIfExists('order_products');
    }
}
