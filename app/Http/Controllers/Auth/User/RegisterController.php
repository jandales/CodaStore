<?php

namespace App\Http\Controllers\Auth\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisterController extends Controller
{

    public function __construct(){
        $this->middleware(['guest']);
    }
    
    public function index(){
        return view('register');
    }
    public function store(Request $request){  

        $request->validate([
            'name' => 'required',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6'
        ]);
        
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
