<?php 

namespace App\Services;

use App\Models\Photo;
use Illuminate\Http\Request;

class ImageServies
{
    public function uploads(Request $request)
    {
        $images = [];

        if (!$request->hasfile('images')) return; 
    
        foreach($request->file('images') as $image){ 
            $name = imageName($image); 
            $location = 'img/test/';
            $path =  $location . $name;
            $photo = Photo::create([        
                'path' => $path,
                'name' => $name,
                'location' => $location
            ]);
            if ($photo) $image->move(public_path($location),$name);
            array_push($images, ["id" => $photo->id, "path" => $photo->path, 'deleted' => 0]);
        }  
    }

    public function delete(Photo $photo)
    {
        $deleted = $photo->delete();  
        if(!$deleted) return back()->with('error','Image cant delete');  
        $path = public_path($photo->path);
        File::delete($path);   
    }
}