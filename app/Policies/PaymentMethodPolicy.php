<?php

namespace App\Policies;

use App\Models\User;
use App\Models\V1\PaymentMethod;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('legal_entity_read');
    }

    public function create(User $user)
    {
        return $user->hasPermission('legal_entity_create');
    }

    public function update(User $user, PaymentMethod $paymentMethod)
    {
        return $user->hasPermission('legal_entity_update');
    }

    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        return $user->hasPermission('legal_entity_delete');
    }
}
