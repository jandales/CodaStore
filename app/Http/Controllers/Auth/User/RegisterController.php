<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserRegisterRequest;

class RegisterController extends Controller
{

    public function __construct()
    {
        $this->middleware(['guest']);
    }
    
    public function index()
    {
        return view('register');
    }

    public function store(UserRegisterRequest $request)
    {

        $validated = $request->validated();
        $validated['password'] = Hash::make($request['password']);
        User::create($validated);
        // auto login
        $credentials = $request->only('email', 'password');
        auth()->attempt($credentials);
        return redirect()->route('account');
        
    }
}
