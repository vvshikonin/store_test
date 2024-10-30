<?php

namespace App\Http\Requests\Invoice;

use App\Http\Requests\Invoice\Request;

class StoreRequest extends Request
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

        $rules = array_merge($mainDataRules, $paymentRules, $deliveryRules, $newProductsRules);

        return $rules;
    }
}
