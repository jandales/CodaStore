<?php
namespace App\Services;

use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;

class UsersServices
{
    use ImageUploadTrait;
    
    public function users()
    {
        return Admin::all()->except(auth()->guard('admin')->user()->id);
    }

    public function store($request)
    {
        return Admin::create([
            'username' => $request->username,
            'email' => $request->email,
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);
        
    }

    public function update($request, Admin $admin)
    {
        $path = null;   

        if(isset($request->image)) $path = $this->upload($request);

        if($request->isSetNewPassword) $admin->password = Hash::make($request->password);   

        $admin->firstname = $request->firstname;
        $admin->lastname = $request->lastname;       
        $admin->role = $request->role;
        $admin->imagePath  = $path ?? $admin->imagePath;
        $admin->contact_number = $request->contact_number;
        $admin->street = $request->street;
        $admin->city_town =$request->city_town;
        $admin->postalcode_zip = $request->postalcode_zip;
        $admin->country_region = $request->country_region;       
        $admin->save();
        
        return $admin;
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
