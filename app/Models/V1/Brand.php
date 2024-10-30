<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'marginality',
        'maintained_marginality'
    ];

    /**
     * Фильтрует бренды по полю "Имя"
     */
    public function scopeNameFilter($query, $name_filter) {
        $query->where('name', 'like', '%'.$name_filter.'%');
    }

    /**
     * Определяет отношение с моделью Product "один ко многим"
     */
    public function products() {
        return $this->hasMany(Product::class);
    }
}
