<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Traits\ClearCache;
use App\Models\V1\Contracts\Cacheable;

class LegalEntity extends Model implements Cacheable
{
    use HasFactory;
    use ClearCache;

    protected $fillable = [
        'name'
    ];

    public function cacheKeys(): array
    {
        return [
            'legal_entities_all',
        ];
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }
}
