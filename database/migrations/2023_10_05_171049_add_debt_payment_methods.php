<?php

use App\Models\V1\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDebtPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PaymentMethod::create([
            'legal_entity_id' => 1,
            'name' => 'Оплата за счёт долга',
            'type' => 3,
        ]);

        PaymentMethod::create([
            'legal_entity_id' => 2,
            'name' => 'Оплата за счёт долга',
            'type' => 3,
        ]);
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
