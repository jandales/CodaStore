<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForgotPassword;
use App\Mail\forgorPasswordMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware(['guest']);
    }

    public function index()
    {        
        return view('forgotpassword');
    }

    public function request(Request $request)
    {
                
        $request->validate([
            'email' => 'required|email|string'
        ]);

        $email = $request->email;
        $token = $request->_token;

        $expiry = date('Y-m-d H:i:s', strtotime("+1 day"));
        
        // check email if  registered
        $register = User::where('email', $email)->first();

        if($register == null) return back()->with('error',"Email not yet Register");

        //// create request
        $request_created =  ForgotPassword::create([
                'email' => $email,
                'token' => $token,
                'created_at' =>  $expiry,
        ]);

        if(!$request_created) return back()->with('error','Unknown error');
        

        /// create url to reset password
        $url = url("/reset_password/{$token}");
  
        //// mail the url into a user
        Mail::to("to@example.com")->send(new forgorPasswordMail($url));

        return back()->with('success','Please check your Email to reset your password');
    }

   
}
