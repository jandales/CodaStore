<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{


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

   
}
