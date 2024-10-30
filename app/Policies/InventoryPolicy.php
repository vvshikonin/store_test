<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\Inventory;
use Illuminate\Auth\Access\HandlesAuthorization;

class InventoryPolicy
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
        return $user->hasPermission('inventory_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Inventory  $invoice
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Inventory $inventory)
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
        return $user->hasPermission('inventory_create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Inventory  $inventory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Inventory $inventory)
    {
        return $user->hasPermission('inventory_update');
    }

    /**
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Inventory  $inventory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function correct(User $user, Inventory $inventory)
    {
        return $user->hasPermission('inventory_correction');
    }
    
    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Inventory  $inventory
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Inventory $inventory)
    {
        return $user->hasPermission('inventory_delete');
    }
}
