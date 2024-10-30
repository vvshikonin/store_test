<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\V1\Traits\ClearCache;
use App\Models\V1\Contracts\Cacheable;

class PaymentMethod extends Model implements Cacheable
{
    use HasFactory;
    use SoftDeletes;
    use ClearCache;

    protected $fillable = [
        'name',
        'legal_entity_id',
        'type'
    ];

    public function cacheKeys(): array
    {
        return [
            'payment_methods_with_type_3',
            'payment_methods_all'
        ];
    }

    public function legalEntity()
    {
        return $this->belongsTo(LegalEntity::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('excludeType3', function ($builder) {
            $builder->where('type', '!=', 3);
        });
    }
}
