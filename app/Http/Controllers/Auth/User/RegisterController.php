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
        $user = User::create($validated);
        event(new \Illuminate\Auth\Events\Registered($user)); // sends the confirm-email link
        // auto login
        $credentials = $request->only('email', 'password');
        auth()->attempt($credentials);
        return redirect()->route('account')->with('success', 'Welcome to Coda. We sent a link to ' . $user->email . ' to confirm your email.');

    }
}
