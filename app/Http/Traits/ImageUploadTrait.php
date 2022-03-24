<?php
namespace App\Http\Traits;

use App\Models\Photo;
use Illuminate\Http\Request;

trait ImageUploadTrait {
  
    private $location = 'img/test/';

    public function upload(Request $request, $name = 'image')
    {      
        if (!$request->hasFile($name) ) return null;
        $image = $request->file($name);       
        $name = $this->makeImageName($image);         
        $path = $this->location . $name;
        $photo = $this->storePhoto($path, $name, $this->location);
        if ($photo) $image->move(public_path($this->location),$name);
        return $photo->path;
    }

    private function storePhoto($path, $name, $location)
    {
        $photo = Photo::create([        
            'path' => $path,
            'name' => $name,
            'location' => $location
        ]);

        return $photo;
    }

    private function makeImageName($image){
        $size = $image->getSize(); 
        $type = $image->getClientOriginalExtension();       
        return date('Y') . time() . $size . "." . $type;
    }

}