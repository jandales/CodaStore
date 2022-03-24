<?php

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


function imageName($image){
    $size = $image->getSize(); 
    $type = $image->getClientOriginalExtension();
    $name = date('Y') . time() . $size . "." . $type;
    return $name;
}

function makeProductImage($image, $slug, $index){
    $type = $image->getClientOriginalExtension();
    $name = $slug . '-' . date('Y') . time() . '-' .$index . '.' . $type;      
    return $name;
}



function ImageUpload($file,$location){    

    $image = $file['image'];
    $name = $file['name'];  
    $image->move(public_path($location),$name);
}


function imageDelete($image){
    if($image != null){
        File::delete($image);
    }    
}


function path_product(){
    return "img/products/";
}

function default_product_image(){
    return "img/products/default.jpg";
}

function slug($name)
{   
    return Str::slug(strtolower($name));
}

function productSlug($name)
{
    return slug($name);     
}

function makeSlug($name,$slug){
    if($slug == null) return slug($name);
    return slug($slug);
}


function createFolder($name)
{
    $path = public_path('img/products/' . $name);   
    //// check if path is already exist
    if(!File::exists($path)) {
        // make folder
        File::makeDirectory($path, 0775, true);         
    }

    return $path;

    
}

function renameFolder($oldname,$newname){
        /// rename foldername
       rename('img/products/'. $oldname ,'img/products/'.$newname);
}

function dimensions($properties){
    return $properties['length'] .' x '. $properties['width'] .' x ' . $properties['height'];
}











