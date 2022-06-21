<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Admin;
use App\Models\Coupon;
use Illuminate\Auth\Access\HandlesAuthorization;

class CouponPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the Admin can view any models.
     *
     * @param  \App\Models\Admin  $Admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(Admin $admin)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can view the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(Admin $admin, Coupon $coupon)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can create models.
     *
     * @param  \App\Models\Admin  $Admin
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(Admin $admin)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can update the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(Admin $admin, Coupon $coupon)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can delete the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(Admin $admin, Coupon $coupon)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can restore the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(Admin $admin, Coupon $coupon)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can permanently delete the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(Admin $admin, Coupon $coupon)
    {
        return $admin->role == Role::IS_ADMIN;
    }
}
