<?php

namespace Database\Factories\V1;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\V1\Product;

class InvoiceProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $productID = Product::inRandomOrder()->first()->id;
        $amount = $this->faker->numberBetween(1, 50);
        $received = $this->faker->numberBetween(0, $amount);
        $refused = $this->faker->numberBetween(0, $amount - $received);
        $planned_delivery_date_from = $this->faker->date();
        $planned_delivery_date_to = $this->faker->dateTimeBetween($planned_delivery_date_from)->format('Y-m-d');

        return [
            'product_id' => $productID,
            'price' => $this->faker->randomFloat(2, 500, 5000),
            'amount' =>  $amount,
            'received' => $received,
            'refused' =>  $refused,
            'planned_delivery_date_from' => $planned_delivery_date_from,
            'planned_delivery_date_to' => $planned_delivery_date_to,
        ];
    }
}
