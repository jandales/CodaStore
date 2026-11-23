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

    /** Largest photo accepted, in kilobytes (the Media card checks the same limit first). */
    public const MAX_KB = 5120;

    public function uploads(Request $request)
    {
        $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_KB],
        ], [
            'images.required' => 'Choose at least one photo.',
            'images.max' => 'Upload at most 10 photos at a time.',
            'images.*.image' => 'Only photos can be uploaded (JPG, PNG or WebP).',
            'images.*.mimes' => 'Only JPG, PNG or WebP photos can be uploaded.',
            'images.*.max' => 'Each photo must be 5 MB or smaller.',
            'images.*.uploaded' => 'A photo could not be uploaded. It may be larger than 5 MB.',
        ]);

        $images = $this->imageServices->uploads($request);
        return response()->json(['status' => 'success',  'images' => $images]);  
    } 

    public function unlink(Photo $photo)
    {  
        $deleted = $this->imageServices->delete($photo);
        return response()->json(['deleted' => $deleted]);
  
    }
}
