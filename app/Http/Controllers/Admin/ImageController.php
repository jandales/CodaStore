<?php

namespace App\Http\Controllers\Admin;
use App\Models\Photo;
use Illuminate\Http\Request;
use App\Services\ImageServices;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    private $imageServices;

    public function __construct(ImageServices $imageServices)
    {
        $this->imageServices = $imageServices;
    }

    public function uploads(Request $request)
    {                  
        $images = $this->imageServices->uploads($request);
        return response()->json(['status' => 'success',  'images' => $images]);  
    } 

    public function unlink(Photo $photo)
    {  
        $this->imageServices->delete($photo);
        return response()->json(['deleted' => $deleted]);  
  
    }
}
