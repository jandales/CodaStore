<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Admin;
use App\Models\Inbox;
use Illuminate\Auth\Access\HandlesAuthorization;

class InboxPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the Admin can view any models.
     *
     * @param  \App\Models\Admin  $Admin
     * @return mixed
     */
    public function viewAny(Admin $Admin)
    {
        return $Admin->role === Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can view the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Inbox  $inbox
     * @return mixed
     */
    public function view(Admin $Admin, Inbox $inbox)
    {
        return $Admin->role === Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can create models.
     *
     * @param  \App\Models\Admin  $Admin
     * @return mixed
     */
    public function create(Admin $Admin)
    {
        //
    }

    /**
     * Determine whether the Admin can update the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Inbox  $inbox
     * @return mixed
     */
    public function update(Admin $Admin, Inbox $inbox)
    {
        return $Admin->role === Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can delete the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Inbox  $inbox
     * @return mixed
     */
    public function delete(Admin $Admin, Inbox $inbox)
    {
        return $Admin->role === Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can restore the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Inbox  $inbox
     * @return mixed
     */
    public function restore(Admin $Admin, Inbox $inbox)
    {
        return $Admin->role === Role::IS_ADMIN;
    }

    /**
     * Determine whether the Admin can permanently delete the model.
     *
     * @param  \App\Models\Admin  $Admin
     * @param  \App\Models\Inbox  $inbox
     * @return mixed
     */
    public function forceDelete(Admin $Admin, Inbox $inbox)
    {
        return $Admin->role === Role::IS_ADMIN;
    }
}
