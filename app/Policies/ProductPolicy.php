<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
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
        return $user->hasPermission('product_read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Product  $product
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Product $product)
    {
        return $user->hasPermission('product_show');
    }
    public function homeSearch(User $user)
    {
        return $user->hasPermission('product_home_search');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->hasPermission('product_create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Product  $product
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Product $product)
    {
        return $user->hasPermission('product_update');
    }

    public function saleUpdate(User $user, Product $product)
    {
        return $user->hasPermission('product_is_sale_update');
    }
    

    /**
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Product  $product
     * @return \Illuminate\Auth\Access\Response|bool
     */
    
    public function merge(User $user)
    {
        return $user->hasPermission('product_merge');
    }
    
    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\V1\Product  $product
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Product $product)
    {
        return $user->hasPermission('product_delete');
    }

    public function correct(User $user, Product $product)
    {
        return $user->hasPermission('product_stocks_correct');
    }
}
