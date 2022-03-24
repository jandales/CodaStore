<?php

namespace App\Http\Controllers\Admin;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function uploads(Request $request, ImageServices $imageServices)
    {                  
        $services->uploads();
        return response()->json(['status' => 'success',  'images' => $images]);  
    } 

    public function unlink(Photo $photo)
    {  
        $services->delete($photo);
         return response()->json(['deleted' => $deleted]);  
  
    }
}
