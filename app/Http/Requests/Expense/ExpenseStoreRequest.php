<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\V1\Expenses\Expense;
use Illuminate\Support\Facades\Gate;

class ExpenseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', Expense::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => 'nullable|string',
            'legal_entity_id' => 'required|integer',
            'payment_method_id' => 'required|integer',
            'is_paid' => 'required|integer',
        ];
    }
}
