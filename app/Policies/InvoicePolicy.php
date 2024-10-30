<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
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
        return $user->hasPermission('invoice_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user)
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
        return $user->hasPermission('invoice_create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user)
    {
        return $user->hasPermission('invoice_update');
    }

    /**
     * Определяет возможность пользователя обновлять поле `payment_confirm`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function paymentConfirmUpdate(User $user)
    {
        return $user->hasPermission('invoice_payment_confirm_update');
    }

    /**
     * Определяет возможность пользователя обновлять `InvoiceProduct` связанные с `Invoice`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function positionUpdate(User $user)
    {
        return $user->hasPermission('invoice_position_update');
    }

    /**
     * Определяет возможность пользователя создовать `InvoiceProduct` связанные с `Invoice`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function positionCreate(User $user)
    {
        return $user->hasPermission('invoice_position_create');
    }


    /**
     * Определяет возможность пользователя удалять `InvoiceProduct` связанные с `Invoice`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function positionDelete(User $user)
    {
        return $user->hasPermission('invoice_position_delete');
    }


    /**
     * Определяет возможность пользователя обновлять поле `received`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function receivedUpdate(User $user)
    {
        return $user->hasPermission('invoice_credited_update');
    }

    /**
     * Определяет возможность пользователя обновлять поле `refused`.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function refusedUpdate(User $user)
    {
        return $user->hasPermission('invoice_refund_update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user)
    {
        return $user->hasPermission('invoice_delete');
    }
}
