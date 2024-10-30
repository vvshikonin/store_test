<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PaymentMethodResource;
use App\Models\V1\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', PaymentMethod::class);

        // Отключаем глобальный скоуп если `withType3` в запросе равно `true`,
        // что бы загрузить все способы оплаты, включая оплаты за счёт долга.
        if ($request->boolean('withType3')) {
            $paymentMethods = PaymentMethod::withoutGlobalScope('excludeType3')->get();
        } else {
            $paymentMethods = PaymentMethod::all();
        }

        return PaymentMethodResource::collection($paymentMethods);
    }

    public function store(Request $request)
    {
        $this->authorize('create', PaymentMethod::class);

        $newPaymentMethod = new PaymentMethod;

        $newPaymentMethod->name = $request->get('name');
        $newPaymentMethod->type = $request->get('type');
        $newPaymentMethod->legal_entity_id = $request->get('legal_entity_id');

        $newPaymentMethod->save();

        return $newPaymentMethod;
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::find($id);

        $this->authorize('update', $method);

        $method->name = $request->get('name');
        $method->type = $request->get('type');

        $method->save();

        return $method;
    }

    public function destroy($id)
    {
        // $method = PaymentMethod::find($id);

        // $this->authorize('delete', $method);

        // $method->delete();

        // return $id;
    }
}
