<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\V1\User;

class UserFilterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'table',
        'template_data'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
