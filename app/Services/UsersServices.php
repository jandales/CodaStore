<?php
namespace App\Services;

use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;

class UsersServices
{
    use ImageUploadTrait;

    private $location = '/img/avatar/admin/';

    public function users()
    {

        return Admin::all()->except(auth()->guard('admin')->user()->id);

    }

    public function store(Request $request)
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['permissions'] = self::permissionsFor((int) $validated['role'], $request->input('permissions', []));
        $admin = Admin::create($validated);

        ActivityLog::record('staff.created', $admin, 'Added ' . $admin->username . ' (' . implode(', ', $admin->permissionLabels() ?: ['no access']) . ')');

        return $admin;
    }

    public function update($request, Admin $admin)
    {
        $path = null;

        if(isset($request->image)) $path = $this->upload($request, $this->location);

        if($request->isSetNewPassword) $admin->password = Hash::make($request->password);

        $admin->firstname = $request->firstname;
        $admin->lastname = $request->lastname;
        $admin->imagePath  = $path ?? $admin->imagePath;
        $admin->contact_number = $request->contact_number;
        $admin->street = $request->street;
        $admin->city_town =$request->city_town;
        $admin->postalcode_zip = $request->postalcode_zip;
        $admin->country_region = $request->country_region;

        // Access is only changed from the staff form, and never on your own account.
        if ($request->has('role') && !$admin->is(auth('admin')->user()) && auth('admin')->user()->is_admin()) {
            $before = $admin->permissionLabels();
            $admin->role = (int) $request->role === Admin::ROLE_ADMINISTRATOR ? Admin::ROLE_ADMINISTRATOR : Admin::ROLE_STAFF;
            $admin->permissions = self::permissionsFor($admin->role, (array) $request->input('permissions', []));
            $after = $admin->permissionLabels();
            if ($before !== $after) {
                ActivityLog::record('staff.access', $admin, 'Changed access for ' . $admin->username . ': ' . (implode(', ', $before) ?: 'none') . ' → ' . (implode(', ', $after) ?: 'none'));
            }
        }

        $admin->save();

        return $admin;
    }

    /** Administrators don't need a list; staff keep only known permission keys. */
    public static function permissionsFor(int $role, array $permissions): ?array
    {
        if ($role === Admin::ROLE_ADMINISTRATOR) {
            return null;
        }

        return array_values(array_intersect(array_keys(Admin::PERMISSIONS), $permissions));
    }

    public function destroy($request)
    {
        foreach($request->selected as $id)
        {
            $admin = Admin::find($id);
            $admin->delete();
        }
    }

    public function updateRoleTo($request)
    {
        if($request->role == '')  return ['status' =>  'error', 'message' => 'Please Select role to update'];

        foreach($request->selected as $id)
        {
            $admin = Admin::find($id);
            $admin->role = $request->role;
            $admin->save();
        }
        return ['status' => 'success', 'message' => 'users successfully Deleted'];
    }




}
