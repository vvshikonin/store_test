<?php

namespace App\Http\Resources\V1\Invoice;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\Invoice\InvoiceProductResource;
use App\Http\Resources\V1\Invoice\RefusesHistoryResource;
use App\Http\Resources\V1\TransactionableResource;
use App\Traits\DateHandle;

/**
 * Преобразет ответ `json` для `App\Model\V1\Invoice`.
 */
class InvoiceResource extends JsonResource
{
    use DateHandle;

    public $refusesHistory;

    public function __construct($resource, $refusesHistory = [])
    {
        parent::__construct($resource);
        $this->refusesHistory = $refusesHistory;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'date' => $this->formatIfSet($this->date, 'Y-m-d'),
            'status' => $this->status,
            'status_set_at' => $this->status_set_at,
            'delivery_type' => $this->delivery_type,
            'comment' => $this->comment,
            'payment_date' => $this->formatIfSet($this->payment_date, 'Y-m-d'),
            'payment_order_date' => $this->formatIfSet($this->payment_order_date, 'Y-m-d'),
            'payment_status' => $this->payment_status,
            'payment_confirm' => $this->payment_confirm,
            'debt_payment' => $this->debt_payment,
            'invoice_files' => is_string($this->invoice_files) ? json_decode($this->invoice_files) : $this->invoice_files,
            'payment_files' => is_string($this->payment_files) ? json_decode($this->payment_files) : $this->payment_files,
            'contractor_id' => $this->contractor_id,
            'contractor_name' => $this->when($this->contractor_name !== null, $this->contractor_name),
            'legal_entity_id' => $this->legal_entity_id,
            'legal_entity_name' => $this->when($this->legal_entity_name !== null, $this->legal_entity_name),
            'payment_method_id' => $this->payment_method_id,
            'payment_method_name' => $this->when($this->payment_method_name !== null, $this->payment_method_name),
            'total_sum' => $this->when($this->total_sum !== null, $this->total_sum),
            'received_at' => $this->received_at,
            'min_delivery_date' => $this->min_delivery_date,
            'max_delivery_date' => $this->max_delivery_date,
            'products' => InvoiceProductResource::collection($this->whenLoaded('invoiceProducts')),
            'transactions' => TransactionableResource::collection($this->whenLoaded('transactions')),
            'payment_history' => PaymentHistoryResource::collection($this->whenLoaded('paymentHistory')),
            'refuses_history' => $this->refusesHistory,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => $this->whenLoaded('creator', $this->creator),
            'updater' => $this->whenLoaded('updater', $this->updater),
            'is_edo' => $this->is_edo,
        ];
    }
}
