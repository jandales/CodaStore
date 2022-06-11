<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserPaymentOption;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPaymentOptionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->is_current_user();
    
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPaymentOption  $userPaymentOption
     * @return mixed
     */
    public function view(User $user, UserPaymentOption $userPaymentOption)
    {
        return $user->id === $userPaymentOption->user_id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->is_current_user();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPaymentOption  $userPaymentOption
     * @return mixed
     */
    public function update(User $user, UserPaymentOption $userPaymentOption)
    {
        return $user->id === $userPaymentOption->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPaymentOption  $userPaymentOption
     * @return mixed
     */
    public function delete(User $user, UserPaymentOption $userPaymentOption)
    {
        return $user->id === $userPaymentOption->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPaymentOption  $userPaymentOption
     * @return mixed
     */
    public function restore(User $user, UserPaymentOption $userPaymentOption)
    {
        return $user->id === $userPaymentOption->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\UserPaymentOption  $userPaymentOption
     * @return mixed
     */
    public function forceDelete(User $user, UserPaymentOption $userPaymentOption)
    {
        return $user->id === $userPaymentOption->user_id;
    }
}
