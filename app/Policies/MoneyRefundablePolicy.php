<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\MoneyRefundable;
use Illuminate\Auth\Access\HandlesAuthorization;

class MoneyRefundablePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->hasPermission('money_refund_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\MoneyRefundable  $moneyRefundable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, MoneyRefundable $moneyRefundable)
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\MoneyRefundable  $moneyRefundable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, MoneyRefundable $moneyRefundable)
    {
        return $user->hasPermission('money_refund_update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\MoneyRefundable  $moneyRefundable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MoneyRefundable $moneyRefundable)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\MoneyRefundable  $moneyRefundable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, MoneyRefundable $moneyRefundable)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\MoneyRefundable  $moneyRefundable
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, MoneyRefundable $moneyRefundable)
    {
        //
    }
}
