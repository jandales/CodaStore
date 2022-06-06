<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\ImageUploadTrait;

class CustomerServices {

    use ImageUploadTrait ;
        
    public function list()
    {
        return User::where('delete_at', 0)->paginate(10);
    }
    
    public function update($request)
    {
         $user = auth()->user();
         $user->name = $request->name;
         $user->contact = $request->contact;
         $user->dateofbirth = $request->dateofbirth;
         $user->age = $request->age;
         $user->save();
    }

    public function destroy(User $user)
    {         
        $user->delete_at = 1;
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

        $default_image_path = '/img/avatar/default-avatar.jpg';
    
        if (!$request->hasFile('avatar'))  return ['error' => 'Please select image to upload'];

        $user = auth()->user(); 
        $oldpath = $user->imagePath;
      
   
        $path = $this->upload($request, $location, 'avatar');      
        $user->imagePath = $path;
        $user->save();
        
        if (!$default_image_path == $oldpath) {
            File::delete(public_path() .  $oldpath);
        }   
       
        return ['success' => 'Image Successfully upload'];
    }

    public function deleteSelectedItem($users)
    {      
        foreach($users as $id){ 
            $id = decrypt($id);
            $user = User::find($id);
            $user->delete_at = 1;
            $user->save();   
        }   
    }

    public function search(Request $request)
    {
        return User::Search($request->keyword)->paginate(10);
    }


}