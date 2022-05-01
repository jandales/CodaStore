<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    public function store(){

        auth()->logout();

        return redirect()->route('home');
        
    }

    public function logout(){
        
    
        Auth::logout();  

        return redirect()->route('admin.login');

        // if(session()->has('adminLogined')){
        //     session()->pull('adminLogined');
        //     return redirect()->route('admin-login');
        // }

    }
}
