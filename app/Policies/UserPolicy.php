<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\Admin $user
     * @return mixed
     */
    public function viewAny(Admin $admin)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(Admin $admin, User $user)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(Admin $admin)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(Admin $admin, User $user)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function delete(Admin $admin, User $user)
    {
        return $admin->role == Role::IS_ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function restore(Admin $admin, User $user)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function forceDelete(Admin $admin, User $user)
    {
        //
    }








      /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User $user
     * @return mixed
     */
    public function clientViewAny(User $user)
    {
        return $user->is_current_user();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $model
     * @return mixed
     */
    public function clientView(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function clientCreate(User $user)
    {
        return $user->is_current_user();
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function clientUpdate(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function clientDelete(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\Admin  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function clientRestore(User $user, User $model)
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function clientForceDelete(User $user, User $model)
    {
        return $user->id === $model->id;
    }
}
