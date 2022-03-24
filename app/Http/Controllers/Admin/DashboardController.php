<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index(){
        
        // count if theres record in admin table      
 
         $admin = Admin::all();
 
         if(session()->has('adminLogined')) {
             return redirect()->route('admin.dashboard');
         } 
 
         if(count($admin) == 0){
             return redirect()->route('admin-register');
         }
             return redirect()->route('admin.login');
 
     }
 
     public function dashboard(){
         $id =  session('adminLogined');    
         
         $admin = Admin::find($id);
      
         return view('admin.index')->with('admin', $admin);
 
     }
}
