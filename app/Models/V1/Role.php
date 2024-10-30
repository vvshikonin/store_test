<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\V1\Permission;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function scopeNameFilter($query, $name_filter)
    {
        $query->where('name', 'like', '%' . $name_filter . '%');
    }

    public function permissions()
    {
        return $this->morphToMany(Permission::class, 'permissionable');
    }
}
