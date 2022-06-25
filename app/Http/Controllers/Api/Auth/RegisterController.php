<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRegisterRequest;

class RegisterController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        
        $validated = $request->validated();

        $validated['password'] = Hash::make($request['password']);

        $user = User::create($validated);       
  
        return response()->json(['user' => $user]);
    }
}
