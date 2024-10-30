<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissionable extends Model
{
    use HasFactory;

    protected $fillable = [
        'permissionable_id',
        'permission_id',
        'permissionable_type'
    ];
}
