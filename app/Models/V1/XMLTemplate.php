<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XMLTemplate extends Model
{
    use HasFactory;

    protected $table = 'xml_templates';

    protected $guarded = [
        'id',
    ];
    protected $fillable = [
        'name',
        'brand_ids',
        'xml_link'
    ];

    protected $casts = [
        'brand_ids' => 'array',
    ];

    protected $appends = ['brand_names'];

    public function getBrandNamesAttribute()
    {
        $brandIds = $this->brand_ids;

        return Brand::whereIn('id', $brandIds)->pluck('name')->toArray();
    }
}
