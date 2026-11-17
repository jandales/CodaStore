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
        return User::paginate(10);
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
        $user->delete();   
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

    
        if (!$request->hasFile('avatar'))  return ['error' => 'Please select image to upload', 'status' => 500];

        $user = auth()->user(); 
        $oldpath = $user->imagePath;
      
   
        $path = $this->upload($request, $location, 'avatar');      
        $user->imagePath = $path;
        $user->save();
        
        if ($oldpath !== $path) {
            self::deleteUploadedAvatar($oldpath);
        }   
        return $user;
        // return ['success' => 'Image Successfully upload'];
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

    public function removeImage()
    {
        $user = auth()->user(); 
        self::deleteUploadedAvatar($user->imagePath);

        $user->imagePath = null;
        $user->save();
        return $user;
         
    }

    /**
     * Deletes a customer's own uploaded photo. Shared images (the old stock default, the
     * placeholder), Google photo URLs and anything outside /img/avatar/ are never touched.
     */
    private static function deleteUploadedAvatar(?string $path): void
    {
        $shared = ['/img/avatar/default-avatar.jpg', '/img/avatar/default-avatar.png', User::AVATAR_PLACEHOLDER];
        if (!$path || in_array($path, $shared, true) || !preg_match('#^/img/avatar/[A-Za-z0-9._-]+$#', $path)) {
            return;
        }

        File::delete(public_path($path));
    }
}
