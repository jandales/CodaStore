<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Admin  $user
     * @return mixed
     */
    public function viewAny(Admin $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function view(Admin $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Admin  $user
     * @return mixed
     */
    public function create(Admin $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function update(Admin $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function delete(Admin $user, Order $order)
    {
        return $user->role == \App\Models\Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Admin $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function restore(Admin $user, Order $order)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @return mixed
     */
    public function forceDelete(User $user, Order $order)
    {
        //
    }
}
