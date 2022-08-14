<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\CustomerServices;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    private $services;

    public function __construct(CustomerServices $services)
    {   
        $this->services = $services;
    }

    public function user()
    {
        return auth()->user();
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'dateofbirth' => 'required',
            'age' => 'required',          
        ]);

        $user = auth()->user();      
        $user->name = $request->name;
        $user->contact = $request->contact;
        $user->dateofbirth = $request->dateofbirth;
        $user->age = $request->age;
        $user->save();
        
        return $user;
    }

    public function upload(Request $request)
    {        
        $user = $this->services->updateAvatar($request);
        return response()->json($user);
    }

    public function removeImage()
    {
        $user = $this->services->removeImage();
        return response()->json($user);
    }



   
}
