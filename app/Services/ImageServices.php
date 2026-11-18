<?php

namespace App\Services;

use App\Models\Photo;
use Illuminate\Http\Request;

class ImageServices
{
    public function uploads(Request $request)
    {
        $images = [];

        if (!$request->hasfile('images')) return;

        foreach($request->file('images') as $image){
            $name = imageName($image);
            $location = '/img/test/';
            $path =  $location . $name;
            $photo = Photo::create([
                'path' => $path,
                'name' => $name,
                'location' => $location
            ]);
            if ($photo) $image->move(public_path($location),$name);
            array_push($images, ["id" => $photo->id, "path" => $photo->path, 'deleted' => 0]);
        }
        return $images;
    }

    public function delete(Photo $photo)
    {
        $deleted = (bool) $photo->delete();
        // Only remove the file when no other product, photo or variant uses it.
        if ($deleted) \App\Services\Products\ProductImageFiles::deleteIfUnused($photo->path);

        return $deleted;
    }
}