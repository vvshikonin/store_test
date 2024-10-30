<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('homeSearch', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'searchStringProduct' => 'nullable|required_without:searchStringOrder|string|min:3',
            'searchStringOrder' => 'nullable|required_without:searchStringProduct|string|min:3',
        ];
    }

    public function messages()
    {
        return [
            'searchStringProduct.required' => 'Поле "Поиск по товарам" является обязательным.',
            'searchStringProduct.min' => 'Поле "Поиск по товарам" должно содержать минимум 3 символа.',
            'searchStringOrder.required' => 'Поле "Поиск по заказу" является обязательным.',
            'searchStringOrder.min' => 'Поле "Поиск по заказу" должно содержать минимум 3 символа.',
        ];
    }
}
