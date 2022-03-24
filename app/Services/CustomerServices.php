<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;

class CustomerServices {

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
        
        if(!$request->hasFile('avatar'))  return back()->with(['error' => 'Please select image to upload']); 
        
        $file = $request->file('avatar');
  
        $user = auth()->user(); 
        $oldimage = $user->imagePath;
  
       
        $image =  ImageMake($file);
        $user->imagePath = $location . $image['name'];
  
        if($user->save()){           
            ImageUpload($image, $location);
            File::delete(public_path() . $oldimage);
        }  
    }

    public function deleteSelectedItem($users)
    {      
      foreach($users as $id)
      { 
         $user = User::find($id);
         $user->delete_at = 1;
         $user->save();
   
      }   
    }


}