<?php 
namespace App\Services\Admin\Auth;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Jobs\ProcessAdminResetPasswordMail;
use App\Models\AdminResetPassword;


class PasswordServices
{
    public function request(Request $request)
    {  

        $admin = Admin::where('email', $request->email)->first();

        if ($admin == null) return ['error' => 'this user is not register'];
       
        $expiry = date('Y-m-d H:i:s', strtotime("+1 day"));

        $created = AdminResetPassword::Create([
            'email' => $request->email,
            'token' => $request->_token,
            'created_at' => $expiry
        ]);

        if(!$created) return ['error' => 'Request cannot proccess'];
        
        $url = url("/admin/reset_password/{$request->_token}");
        
        // Mail::to("to@example.com")->send(new SendEmail($url));
        dispatch(new ProcessAdminResetPasswordMail($url));

        return ['success' => 'Please check your Email to reset your password'];

    }

    public function store(Request $request)
    { 
        $token = $request->_token;
        $password = $request->password; 
        $current_date = date('Y-m-d H:i:s');

        $request_user  = AdminResetPassword::where('token',$token)->first();

        if ($request_user ==  null) return ['error' => 'Request user not verified'];

        $email = $request_user->email;
     
        $expiry_date = $request_user ->created_at; 

       if ($current_date >= $expiry_date) return ['error' => 'Request already expires']; 
    
        Self::update($email,$password); 
  
        DB::delete('delete from admin_reset_password where email = ?', [$email]);
       
        return ['success' => 'Password changed successfully'];

    }

    private function update($email, $password)
    {          
        $admin= Admin::where('email', $email)->first();
        $admin->password = Hash::make($password);
        $admin->save();
        return $admin;
    }
}