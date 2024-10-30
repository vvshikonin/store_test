<?php

namespace App\Policies;

use App\Models\V1\FinancialControl;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancialControlPolicy
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
        return $user->hasPermission('financial_controls_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FinancialControl  $financialControl
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
        return $user->hasPermission('financial_controls_create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FinancialControl  $financialControl
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, FinancialControl $financialControl)
    {
        return $user->hasPermission('financial_controls_update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FinancialControl  $financialControl
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, FinancialControl $financialControl)
    {
        return $user->hasPermission('financial_controls_delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FinancialControl  $financialControl
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, FinancialControl $financialControl)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\FinancialControl  $financialControl
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, FinancialControl $financialControl)
    {
        //
    }
}
