<?php

namespace App\Http\Controllers\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AdminResetPassword as SendEmail;
use App\Models\AdminResetPassword as ResetPassword;

class AdminResetPasswordController extends Controller
{
    public function index(){
        return view('admin.resetpassword');
    }

    public function request(Request $request){

            $request->validate([
                'email' => 'required|email|string',
            ]);

            $email = $request->email;
            $token = $request->_token;     

            //create if this user already exist
            $admin = Admin::where('email', $email)->first();

            if($admin == null) return back()->with('error', 'this user is not register');

            // expiry date of a token
            $expiry = date('Y-m-d H:i:s', strtotime("+1 day"));

            $created = ResetPassword::Create([
                'email' => $email,
                'token' => $token,
                'created_at' => $expiry
            ]);

            if(!$created) return back()->with('error', 'Request cannot proccess');

          
            $url = url("/admin/reset_password/{$token}");

          
            Mail::to("to@example.com")->send(new SendEmail($url));

            return back()->with('success','Please check your Email to reset your password');

    }

    public function reset($token){

        return view('admin.adminresetpassword')->with('token', $token);
    }

    public function store(Request $request){
        
        $request->validate([
            'password' => 'required_with:password_confirmation|string|confirmed|min:6',
            'password_confirmation' => 'required'
        ]);
    
        $token = $request->_token;

        $password = $request->password; 

        $current_date = date('Y-m-d H:i:s');

        $request_user  = ResetPassword::where('token',$token)->first();

        if($request_user ==  null) return redirect()->route('admin-reset-password')->with('error', 'Request user not verified');

        $email = $request_user ->email;

        // check if a token already expire
        $expiry_date = $request_user ->created_at; 

       if($current_date >= $expiry_date){
           return redirect()->route('admin-reset-password')->with('error', 'Request already expires');
       } 

         // change password
        $admin= Admin::where('email', $email)->first();
        $admin->password = Hash::make($password);
        $admin->save();

           // delete the token
        DB::delete('delete from admin_reset_password where email = ?', [$email]);
       
        return back()->with('success', 'Password changed successfully');

    }
}
