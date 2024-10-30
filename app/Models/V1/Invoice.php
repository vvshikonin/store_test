<?php

namespace App\Models\V1;

use App\Traits\BaseFilter;
use App\Traits\UserStamps;
use App\Traits\StringHandle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Счёт
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice select()
 *
 * @property string $comment Комментарий пользователя.
 * @property string $date Дата счета.
 * @property int $contractor_id ID поставщика.
 * @property int $delivery_type Способ доставки.
 * @property string $number Номер счёта.
 * @property int $legal_entity_id ID юр.лица.
 * @property int $payment_method_id ID способа оплаты.
 * @property int $status Статус счета.
 * @property string $status_set_at Дата установки статуса счёта.
 * @property string $file Путь к файлу счёта.
 * @property string $receipt_file Путь к файлу чека.
 * @property int $payment_confirm Статус подтверждения оплаты.
 * @property float $debt_payment Сумма оплаты долгом.
 * @property int $payment_status Статус оплаты.
 * @property string $payment_date Дата оплаты.
 * @property string $payment_order_date Дата заведения платежного поручения.
 * @property string $created_at Время создания.
 * @property string $updated_at Время обновления.
 *
 * @property float $invoiceSum Сумма счёта.
 * @property float $receivedSum Сумма оприходованного товара.
 * @property float $refusedSum Сумма отказа.
 * @property int $productsAmount Количество товара.
 * @property int $receivedAmount Количество оприходованного товара.
 * @property int $refusedAmount Количество товара, от которого отказались.
 * @property int $expectedAmount Количество товара, который ожидается.
 * @property int $processedAmount Количество обработанного товара(Оприход+Отказ).
 * @property Invoice $originalModel Сохранённая модель.
 *
 * @property \Illuminate\Database\Eloquent\Collection $invoiceProducts Коллекция связанных товаров счета.
 * @property \Illuminate\Database\Eloquent\Collection $debtPayments Коллекция связанных оплат долгом.
 * @property LegalEntity $legalEntity Связанное юр.лицо.
 * @property PaymentMethod $paymentMethod Связанный способ оплаты.
 * @property Contractor $contractor Связанный поставщик.
 * @property MoneyRefundable $moneyRefundable Связанный возврат ДС.
 * @property \Illuminate\Database\Eloquent\Collection $transactions Коллеция связанных транзакций товаров.
 *
 * @method \Illuminate\Database\Eloquent\Builder|Invoice productFilter(string $productNameOrSku) Фильтер по товару.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice invoiceNumberFilter(string $invoiceNumber) Фильтер по номеру счета.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice commentFilter(string $comment) Фильтер по комментарию.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice statusFilter(int[] $statuses) Фильтер по статуса счета.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice deliveryTypeFilter(int $type) Фильтер по способу доставки.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice paymentMethodFilter(int $type) Фильтер по способу оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice paymentStatusFilter(int $status) Фильтер по статуса оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice paymentConfirmFilter(int $status) Фильтер по статуса подтверждения оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice invoiceDateFilter(array $dates) Фильтер по дате счета.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice paymentDateFilter(array $dates) Фильтер по дате оплаты.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice sumFilter(array $sums) Фильтер по сумме счёта.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice legalEntityFilter(int $ids) Фильтер по юр.лицу.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice receivedAtFilter(array $dates) Фильтер по дате оприходования.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice createdAtFilter(array $dates) Фильтер по дате создания.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice statusSetAtFilter(array $dates) Фильтер по дате установки статуса.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice contractorsFilter(int $ids) Фильтер по поставщикам.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice plannedDeliveryDateFilter(array $dates) Фильтер по планируемой дате доставки.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice applyFilters(array $filters) Применяет фильтры по переданному массиву.
 *
 * @method \Illuminate\Database\Eloquent\Builder|Invoice joinContractor() Присоединяет таблицу поставщиков к запросу.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice joinLegalEntity() Присоединяет таблицу юр.лиц к запросу.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice joinPaymentMethod() Присоединяют таблицу способов оплаты к запросу.
 * @method \Illuminate\Database\Eloquent\Builder|Invoice joinInvoiceProducts() Присоединяет агрегированную таблицу товаров счёта к запросу.
 */
class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    use BaseFilter;
    use StringHandle;
    use UserStamps;

    protected $fillable = [
        'comment',
        'date',
        'contractor_id',
        'delivery_type',
        'number',
        'legal_entity_id',
        'payment_method_id',
        'debt_payment',
        'payment_order_date',
    ];

    protected $guarded = [
        'status',
        'status_set_at',
        'file',
        'receipt_file',
        'payment_confirm',
        'payment_status',
        'payment_date',
        'is_edo',
    ];

    protected $casts = [
        'date' => 'date',
        'payment_date' => 'datetime',
        'status_set_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $originalModel;

    /**
     * Связь hasMany InvoiceProduct.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceProducts()
    {
        return $this->hasMany(InvoiceProduct::class);
    }

    /**
     * Связь hasMany DebtPayment.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function debtPayments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    /**
     * Связь belongsTo c LegalEntity.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    /**
     * Связь belongsTo c PaymentMethod.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Связь belongsTo Contractor.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /**
     * Связь morphOne c MoneyRefundable.
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function moneyRefundable()
    {
        return $this->morphOne(MoneyRefundable::class, 'refundable');
    }

    /**
     * Связь hasManyThrough c Transactionable.
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function transactions()
    {
        return $this->hasManyThrough(Transactionable::class, InvoiceProduct::class, 'invoice_id', 'transactionable_id', 'id', 'id')
            ->where('transactionable_type', InvoiceProduct::class)->orderBy('created_at', 'desc');
    }

    /**
     * Связь hasMany c InvoicePaymentHistory.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentHistory()
    {
        return $this->hasMany(InvoicePaymentHistory::class);
    }

    /**
     * Фильтр по товару.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $productNameOrSku Название товара или артикул для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeProductFilter($query, $productNameOrSku)
    {
        if ($productNameOrSku !== null) {
            $productIds = Product::select('id')->productFilter($productNameOrSku);
            $invoiceProductInvoiceIds = InvoiceProduct::select('invoice_id')->whereIn('product_id', $productIds);
            $query->whereIn('invoices.id', $invoiceProductInvoiceIds);
        }
    }

    protected function scopeProductPriceFilter($query, $data)
    {
        $invoicesIDs  = InvoiceProduct::select('invoice_products.invoice_id')->betweenFilter('invoice_products.price', $data);
        $query->whereIn('invoices.id', $invoicesIDs);
    }

    /**
     * Фильтр по номеру счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $invoiceNumber Номер счёта для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeInvoiceNumberFilter($query, $invoiceNumber)
    {
        if ($invoiceNumber !== null) {
            $query->whereLike('invoices.number', $invoiceNumber);
        }
    }

    /**
     * Фильтр по комментарию.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $comment Комментарий счёта для фильтрации.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeCommentFilter($query, $comment)
    {
        if ($comment !== null) {
            $query->whereLike('invoices.comment', $comment);
        }
    }

    /**
     * Фильтр по статусу счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $statuses Массив статусов счёта.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeStatusFilter($query, $statuses)
    {
        if ($statuses !== null) {
            if (is_array($statuses)) {
                $query->whereIn('invoices.status', $statuses);
            } else {
                $query->whereIn('invoices.status', [$statuses]);
            }
        }
    }

    /**
     * Фильтр по способу доставки.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|int|null $type способ доставки.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeDeliveryTypeFilter($query, $type)
    {
        if ($type !== null) {
            $query->where('invoices.delivery_type', $type);
        }
    }

    /**
     * Фильтр по способу оплаты.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $type id способа оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePaymentMethodFilter($query, $type)
    {
        if ($type !== null) {
            $query->whereIn('invoices.payment_method_id', $type);
        }
    }

    /**
     * Фильтр по статусу оплаты.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $status статус оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePaymentStatusFilter($query, $status)
    {
        if ($status !== null) {
            $query->where('invoices.payment_status', $status);
        }
    }

    /**
     * Фильтр по подтверждению оплаты.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $status статус подтверждения оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePaymentConfirmFilter($query, $status)
    {
        if ($status !== null) {
            $query->where('invoices.payment_confirm', $status);
        }
    }

    /**
     * Фильтр по дате счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $dates массив дат счёта.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeInvoiceDateFilter($query, $dates)
    {
        $query->betweenFilter('invoices.date', $dates);
    }

    /**
     * Фильтр по дате оплаты.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $dates массив дат оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePaymentDateFilter($query, $dates)
    {
        $query->betweenFilter('invoices.payment_date', $dates);
    }

    /**
     * Фильтр по сумме счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array|null $sums массив дат оплаты.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeSumFilter($query, $sums)
    {
        $invoicesIDs = Invoice::select('invoices.id')
            ->joinInvoiceProducts()
            ->betweenFilter('invoice_products.total_sum', $sums);

        $query->whereIn('invoices.id', $invoicesIDs);
    }

    /**
     * Фильтр по юр. лицу.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $legalEntity id юр. лица.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeLegalEntityFilter($query, $legalEntity)
    {
        if ($legalEntity !== null) {
            $query->where('invoices.legal_entity_id', $legalEntity);
        }
    }

    /**
     * Фильтр по дате оприхода.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates id даты оприхода.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeReceivedAtFilter($query, $dates)
    {
        $invoicesIDs = Invoice::select('invoices.id')
            ->joinInvoiceProducts()
            ->betweenDateTimeFilter('invoice_products.received_at', $dates);

        $query->whereIn('invoices.id', $invoicesIDs);
    }

    /**
     * Фильтр по дате создания.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates дата создания.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeCreatedAtFilter($query, $dates)
    {
        $query->betweenDateTimeFilter('invoices.created_at', $dates);
    }

    /**
     * Фильтр по дате установки статуса.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates дата создания.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeStatusSetAtFilter($query, $dates)
    {
        $query->betweenDateTimeFilter('invoices.status_set_at', $dates);
    }

    /**
     * Фильтр по поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $contractorsIds id поставщиков.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeContractorsFilter($query, $contractorsIds)
    {
        if ($contractorsIds !== null) {
            $query->whereIn('invoices.contractor_id', $contractorsIds);
        }
    }

    /**
     * Фильтрует по основному или дополнительному поставщику.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool|null $isMainContractor Флаг, указывающий, является ли поставщик основным.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeContractorsTypeFilter($query, $isMainContractor)
    {
        if ($isMainContractor !== null) {
            $query->whereHas('contractor', function ($query) use ($isMainContractor) {
                $query->where('is_main_contractor', $isMainContractor);
            });
        }
    }

    /**
     * Фильтр по дате доставки.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates `json` строка с ключами: `start`, `end`, `equal`, `notEqual`.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePlannedDeliveryDateFilter($query, $dates)
    {
        $invoiceProductsInvoiceIDs = InvoiceProduct::select('invoice_id')->plannedDeliveryDateFilter($dates);
        $query->whereIn('invoices.id', $invoiceProductsInvoiceIDs);
    }

    /**
     * Присоединяет таблицу `contractors`. Делает выборку поля `contractors.name` с пcевдонимом `contractor_name`.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeJoinContractor($query)
    {
        $query->selectRaw('contractors.name as contractor_name')
            ->leftJoin('contractors', 'contractors.id', '=', 'invoices.contractor_id');
    }

    /**
     * Присоединяет таблицу legal_entities. Делает выборку поля legal_entities.name с пвевдонимом legal_entity_name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeJoinLegalEntity($query)
    {
        $query->selectRaw('legal_entities.name as legal_entity_name')
            ->leftJoin('legal_entities', 'legal_entities.id', '=', 'invoices.legal_entity_id');
    }

    /**
     * Присоединяет таблицу payment_methods. Делает выборку поля payment_methods.name с пвевдонимом payment_method_name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeJoinPaymentMethod($query)
    {
        $query->selectRaw('payment_methods.name as payment_method_name')
            ->leftJoin('payment_methods', 'payment_methods.id', '=', 'invoices.payment_method_id');
    }


    /**
     * Присоединяет таблицу invoice_products с агрегированными данными.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeJoinInvoiceProducts($query)
    {
        $invocieProductSub = InvoiceProduct::aggregateForInvoice();
        $query->leftJoinSub($invocieProductSub, 'invoice_products', function ($join) {
            $join->on('invoice_products.invoice_id', '=', 'invoices.id');
        });
    }

    /**
     * Фильтр по наличию файла счёта.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeHasInvoiceFile($query, $hasFile)
    {
        if ($hasFile == "1") {
            $query->whereNotNull('invoices.invoice_files')->where('invoices.invoice_files', '!=', '[]');
        } else if ($hasFile == "0") {
            $query->where(function ($query) {
                $query->whereNull('invoices.invoice_files')->orWhere('invoices.invoice_files', '=', '[]');
            });
        }
    }

    /**
     * Фильтр по наличию файла чека.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopeHasReceiptFile($query, $hasFile)
    {
        if ($hasFile == "1") {
            $query->whereNotNull('invoices.payment_files')
                  ->where('invoices.payment_files', '!=', '[]')
                  ->orWhere('invoices.is_edo', true);
        } else if ($hasFile == "0") {
            $query->where(function ($query) {
                $query->whereNull('invoices.payment_files')->orWhere('invoices.payment_files', '=', '[]');
            })->where('invoices.is_edo', false);
        }
    }

    /**
     * Фильтр по дате заведения платежного поручения.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $dates `json` строка с ключами: `start`, `end`, `equal`, `notEqual`.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function scopePaymentOrderDateFilter($query, $dates)
    {
        $query->betweenDateTimeFilter('invoices.payment_order_date', $dates);
    }

    /**
     * Возвращает сумму счёта.
     *
     * @return float
     */
    protected function getInvoiceSumAttribute()
    {
        return $this->invoiceProducts->sum(function ($invoiceProduct) {
            return $invoiceProduct->amount * $invoiceProduct->price;
        });
    }

    /**
     * Возвращает сумму оприходорванного товара.
     *
     * @return float
     */
    protected function getReceivedSumAttribute()
    {
        return $this->invoiceProducts->sum(function ($invoiceProduct) {
            return $invoiceProduct->received * $invoiceProduct->price;
        });
    }

    /**
     * Возвращает сумму товара от которого отказались.
     *
     * @return float
     */
    protected function getRefusedSumAttribute()
    {
        return $this->invoiceProducts->sum(function ($invoiceProduct) {
            return $invoiceProduct->refused * $invoiceProduct->price;
        });
    }

    /**
     * Возвращает количество товара в счёте.
     *
     * @return int
     */
    protected function getProductsAmountAttribute()
    {
        return $this->invoiceProducts->sum('amount');
    }

    /**
     * Возвращает количество оприходованного товара в счёте.
     *
     * @return int
     */
    protected function getReceivedAmountAttribute()
    {
        return $this->invoiceProducts->sum('received');
    }

    /**
     * Возвращает количество товара от которого отказались в счёте.
     *
     * @return int
     */
    protected function getRefusedAmountAttribute()
    {
        return $this->invoiceProducts->sum('refused');
    }

    /**
     * Возвращает количество товара, который ожидается по счёту.
     *
     * @return int
     */
    protected function getExpectedAmountAttribute()
    {
        return $this->productsAmount - $this->receivedAmount - $this->refusedAmount;
    }

    /**
     * Возвращает количество товара, который был обработан по счёту (оприходован или в отказе).
     *
     * @return int
     */
    protected function getProcessedAmountAttribute()
    {
        return $this->receivedAmount + $this->refusedAmount;
    }

    /**
     * Возвращает сохранённую копию модели.
     *
     * @return \App\Models\V1\Invoice
     */
    protected function getOriginalModelAttribute()
    {
        return clone $this->originalModel;
    }

    /**
     * Сохраняет копию текущей модели.
     */
    public function saveOriginalModel()
    {
        $this->originalModel = clone $this;
        $this->originalModel->load(['invoiceProducts']);
    }

    /**
     * Сохраняет файл счёта на сервере и записывает путь в БД.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null $file
     */
    public function saveInvoiceFile($files)
    {
        if (!$files) {
            throw new HttpException(400, 'Файл не предоставлен');
        }


        foreach ($files as $file) {
            $extension = $file->getClientOriginalExtension();
            $fileName = hash('sha256', $file->get());
        }

        $this->file = $file->storeAs('invoices', $fileName . '.' . $extension, 'public');
        $this->save();
    }

    /**
     * Сохраняет файл чека на сервере и записывает путь в БД.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\UploadedFile[]|array|null $file
     */
    public function saveReceiptFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = hash('sha256', $file->get());
        $this->receipt_file = $file->storeAs('invoices', $fileName . '.' . $extension, 'public');
        $this->save();
    }
}
