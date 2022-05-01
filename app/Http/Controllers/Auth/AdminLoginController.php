<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminLoginController extends Controller
{
    public function index()
    {     
        return view('admin.login');
    }

    public function store(Request $request)
    {       
       
        $request->validate([
            'email' => 'required',
            'password' => 'required|min:6'
        ]); 

        $credintails = $request->only('email', 'password');

        if(Auth::guard('admin')->attempt($credintails)){
           return redirect()->route('admin.dashboard'); 
        }

        return back()->with('error','invalid credintails');
        
    }
}
