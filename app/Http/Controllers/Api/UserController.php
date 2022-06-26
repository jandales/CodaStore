<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function find($id)
    {
        return User::find($id);
    }

    public function user($id)
    {
        return response()->json(Self::find($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'contact' => 'required',
            'dateofbirth' => 'required',
            'age' => 'required',          
        ]);

        $user = Self::find($id);      
        $user->name = $request->name;
        $user->contact = $request->contact;
        $user->dateofbirth = $request->dateofbirth;
        $user->age = $request->age;
        $user->save();
        
        return $user;
    }
}
