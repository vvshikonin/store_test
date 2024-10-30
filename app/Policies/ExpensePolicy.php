<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    /**
     * Определить, может ли пользователь просматривать все расходы.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('expenses_read');
    }

    /**
     * Определить, может ли пользователь просмотреть расход.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->hasPermission('expenses_read');
    }

    /**
     * Определить, может ли пользователь создавать расходы.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermission('expenses_create');
    }

    /**
     * Определить, может ли пользователь обновить расход.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $user)
    {
        return $user->hasPermission('expenses_update');
    }

    /**
     * Определить, может ли пользователь удалить расход.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->hasPermission('expenses_delete');
    }
}
