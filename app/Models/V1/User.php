<?php

namespace App\Models\V1;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\V1\Permission;
use App\Models\V1\Role;
use App\Models\V1\UserFilterTemplate;

class User  extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'is_active',
        'password',
        'color'
    ];

    protected $guarded = [
        'avatar',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function personalPermissions()
    {
        return $this->morphToMany(Permission::class, 'permissionable');
    }

    public function filterTemplates()
    {
        return $this->hasMany(UserFilterTemplate::class);
    }

    public function getPermissionsAttribute()
    {
        $roles = $this->roles;

        $permissions = array();

        foreach ($roles as $role) {
            if ($role) {
                foreach ($role->permissions as $permission) {
                    if ($permission) {
                        $permissions[] = $permission;
                    }
                }
            }
        }

        foreach ($this->personalPermissions as $personalPermission) {
            if ($personalPermission) {
                $permissions[] = $personalPermission;
            }
        }

        $permissions = collect($permissions)->unique('id');

        return $permissions;
    }

    /**
     * Проверяет наличие разрешения у пользователя.
     *
     * @param  string $permission
     * @return bool
     */
    function hasPermission($permission)
    {
        if (!$this->permissions->where('name', $permission)->count()) {
            return false;
        }
        return true;
    }

    /**
     * Проверяет наличие хотя бы одного разрешения у пользователя.
     *
     * @param  string ...$permissions
     * @return bool
     */
    function hasAnyPermission(...$permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
    }

    /**
     * Проверяет наличие всех разрешений у пользователя.
     *
     * @param  string ...$permissions
     * @return bool
     */
    function hasAllPermissions(...$permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // Rest omitted for brevity

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
