<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;

class CustomerServices {
    use ImageUploadTrait ;
    public function update($request)
    {
         $user = auth()->user();
         $user->name = $request->name;
         $user->contact = $request->contact;
         $user->dateofbirth = $request->dateofbirth;
         $user->age = $request->age;
         $user->save();
    }

    public function changePassword($request)
    {
        $user = auth()->user(); 
        if(!Hash::check($request->validator,$user->password)) return back()->with('error', 'Invalid credentials');  
        $user->password = Hash::make($request->password);
        $user->save();
    }

    public function updateAvatar($request)
    {
        $location = "/img/avatar/";   
    
        if(!$request->hasFile('avatar'))  return false; 

        $user = auth()->user(); 
        $oldpath = $user->imagePath;
  
        $path = $this->upload($request, $location, 'avatar');      
        $user->imagePath = $path;
        $user->save();
        File::delete(public_path() .  $oldpath);
        return true;
    }

    public function deleteSelectedItem($users)
    {      
        foreach($users as $id){ 
            $user = User::find($id);
            $user->delete_at = 1;
            $user->save();   
        }   
    }


}