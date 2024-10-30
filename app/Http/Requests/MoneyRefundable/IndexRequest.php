<?php

namespace App\Http\Requests\MoneyRefundable;

use Illuminate\Foundation\Http\FormRequest;
use Gate;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('viewAny', Invoice::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    public function filters()
    {
        $filters = [
            'numberFilter',
            'contractorFilter',
            'contractorsFilter',
            'typeFilter',
            'statusFilter',
            'approvedFilter',
            'legalEntityFilter',
            'paymentMethodFilter',
            'sum_start',
            'sum_end',
            'sum_equal',
            'sum_notEqual',
            'actualRefund_start',
            'actualRefund_end',
            'actualRefund_equal',
            'actualRefund_notEqual'
        ];

        $originalRequest = $this->all();

        $this->merge(array_fill_keys($filters, null));
        $this->merge($originalRequest);

        return $this->only($filters);
    }
}
