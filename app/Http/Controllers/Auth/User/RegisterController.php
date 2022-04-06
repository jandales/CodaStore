<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRegisterRequest;

class RegisterController extends Controller
{

    public function __construct(){
        $this->middleware(['guest']);
    }
    
    public function index(){
        return view('register');
    }
    public function store(UserRegisterRequest $request)
    {
        User::create([
            'name' => $request['name'],
            'email' => $request['email'],       
            'password' => Hash::make($request['password'])
        ]);
        $credentials = $request->only('email', 'password');
        auth()->attempt($credentials);
        return redirect()->route('account');
        
    }
}
