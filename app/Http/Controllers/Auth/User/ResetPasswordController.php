<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForgotPassword;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function __construct(){
        $this->middleware(['guest']);
    }

    public function index($token){       
        return view('resetpassword')->with('token',$token);
    }

    public function reset(Request $request){

        $this->validate($request, [         
            'email' => 'email',
            'password' => 'required_with:password_confirmation|string|confirmed|min:6',
            'password_confirmation' => 'required'
        ]);       
   

        $token = $request->token;  
        $password = $request->password; 

        $current_date = date('Y-m-d H:i:s');
        
        // find if a request exist
        $reset = ForgotPassword::where('token',$token)->first(); 
        
        if($reset == null) return redirect('/forgot-password');
        
        $email = $reset->email;

         // check if a token already expire
        $expiry_date = $reset->created_at; 

        if($current_date >= $expiry_date){
            return redirect('/forgot-password')->with('error', 'Request already expires');
        }       
   
        // change password
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($password);
        $user->save();

        // delete the token
        $deleted = DB::delete('delete from password_resets where email = ?', [$email]);

        return redirect()->route('users.login');

    }
}
