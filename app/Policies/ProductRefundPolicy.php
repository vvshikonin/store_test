<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\ProductRefund;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductRefundPolicy
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
        return $user->hasPermission('product_refund_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\ProductRefund  $productRefund
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, ProductRefund $productRefund)
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
     * @param  \App\Models\V1\ProductRefund  $productRefund
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, ProductRefund $productRefund)
    {
        return $user->hasPermission('product_refund_update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\ProductRefund  $productRefund
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, ProductRefund $productRefund)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\ProductRefund  $productRefund
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, ProductRefund $productRefund)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\ProductRefund  $productRefund
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, ProductRefund $productRefund)
    {
        //
    }
}
