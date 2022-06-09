<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the Admin can view any models.
     *
     * @param  \App\Models\Admin  $user
     * @return mixed
     */
    public function viewAny(Admin $user)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can view the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function view(Admin $user, Product $product)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\Admin  $user
     * @return mixed
     */
    public function create(Admin $user)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function update(Admin $user, Product $product)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function delete(Admin $user, Product $product)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function restore(Admin $user, Product $product)
    {
        return $user->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\Product  $product
     * @return mixed
     */
    public function forceDelete(Admin $user, Product $product)
    {
        return $user->role == Role::IS_ADMIN;
    }
}
