<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Filters\DefectFilter;
use App\Traits\UserStamps;
use App\Events\DefectUpdated;

class Defect extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DefectFilter;
    use UserStamps;

    protected $fillable = [
        'order_id',
        'comment',
        'refund_type',
        'product_location',
        'delivery_date',
        'delivery_address',
        'replacement_type',
        'legal_entity_id',
        'payment_method_id',
        'is_completed',
        'created_by',
        'updated_by'
    ];

    protected $guarded = [
        'order_products',
        'avg_price',
        'money_refund_status'
    ];

    /**
     * Запускается при инициализации модели. Устанавливает обработчики событий Eloquent.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::updated(function ($model) {
            event(new DefectUpdated($model));
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderProducts()
    {
        return $this->hasManyThrough(OrderProduct::class, Order::class, 'id', 'order_id', 'order_id', 'id');
    }

    public function moneyRefundable()
    {
        return $this->morphOne(MoneyRefundable::class, 'refundable');
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function getContractorNamesAttribute()
    {
        $names = $this->order->orderProducts->map(function ($orderProduct) {
            return $orderProduct->contractor ?  $orderProduct->contractor->name : null;
        })->unique()->values();

        return $names->implode(', ');
    }

    /**
     * Сохраняет файлы на сервере и записывает пути в массив.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null $file
     */
    public function saveDefectFiles($files)
    {
        $fileData = [];

        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            $fileName = hash('sha256', $file->get());
            $path = $file->storeAs('defects', $fileName . '.' . $extension, 'public');

            $fileData[$path] = $originalName;
        }

        return $fileData;
    }
}
