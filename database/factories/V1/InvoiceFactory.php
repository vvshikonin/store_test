<?php

namespace Database\Factories\V1;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\V1\Contractor;
use App\Models\V1\PaymentMethod;
use App\Models\V1\LegalEntity;
use App\Models\V1\InvoiceProduct;

class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $contractorID = Contractor::inRandomOrder()->first()->id;
        $legalEntityID = LegalEntity::inRandomOrder()->first()->id;
        $paymentMethodID = PaymentMethod::where('legal_entity_id', $legalEntityID)->inRandomOrder()->first()->id;


        $paymentStatus = $this->faker->randomElement([0, 1]);
        $paymentDate = null;
        if ($paymentStatus)
            $paymentDate = $this->faker->date();


        return [
            'number' => $this->faker->words(3, true),
            'date' => $this->faker->date('Y-m-d'),
            'contractor_id' => $contractorID,
            'comment' => $this->faker->optional(0.5, null)->sentence(),
            'payment_method_id' => $paymentMethodID,
            'legal_entity_id' => $legalEntityID,
            'payment_date' => $paymentDate,
            'payment_status' => $paymentStatus,
            'payment_confirm' => $this->faker->randomElement([0, 1]),
            'delivery_type' => $this->faker->optional(0.5, null)->randomElement([0, 1]),
        ];
    }

    public function withInvoiceProducts($count = 1)
    {
        return $this->has(InvoiceProduct::factory()->count($count), 'invoiceProducts');
    }
}
