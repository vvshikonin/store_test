<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Invoice\Request;

class UpdateRequest extends Request
{
    /**
     * Определяет правила валидации параметров запроса.
     *
     * @return array
     */
    public function rules()
    {
        $mainDataRules = $this->mainDataRules();
        $paymentRules = $this->paymentRules();
        $deliveryRules = $this->deliveryRules();
        $newProductsRules = $this->newProductsRules();
        $productsRules = $this->productsRules();
        $deletedProductsRules = $this->deletedProductsRules();

        $rules = array_merge($mainDataRules, $paymentRules, $deliveryRules, $newProductsRules, $productsRules, $deletedProductsRules);

        return $rules;
    }
}
