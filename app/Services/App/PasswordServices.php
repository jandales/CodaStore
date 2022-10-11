<?php

namespace App\Services\App;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForgotPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessUserForgotPasswordMail;

class PasswordServices
{
    public function request($request)
    {            
     
        $email = $request->email;
        $token = $request->_token;

        $expiry = date('Y-m-d H:i:s', strtotime("+1 day"));
        
        // check email if  registered
        $register = User::where('email', $email)->first();

        if($register == null) return ["error" => "Email not yet Register"];

        //// create request
        ForgotPassword::create([
            'email' => $email,
            'token' => $token,
            'created_at' =>  $expiry,
        ]);       
        

        /// create url to reset password
        $url = url("/reset_password/{$token}");  
    

        dispatch(new ProcessUserForgotPasswordMail($email,$url));
      

        return ['success' => 'Please check your Email to reset your password'];
    }


    public function reset(Request $request)
    {         
        $token = $request->token;  
        $password = $request->password; 

        $current_date = date('Y-m-d H:i:s');
        
        // find if a request exist
        $reset = ForgotPassword::where('token',$token)->first(); 
        
        if($reset == null) return [false];  
        
        $email = $reset->email;

         // check if a token already expire
        $expiry_date = $reset->created_at; 

        if($current_date >= $expiry_date)  return [false, 'error' => 'Request already expires'];        
              
   
        // change password
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($password);
        $user->save();

        // delete the token
        $deleted = DB::delete('delete from password_resets where email = ?', [$email]);

        return true;

    }

}