<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserLoginRequest;
use Laravel\Socialite\Facades\Socialite;

class UserLoginController extends Controller
{

    public function __construct()
    {
        $this->middleware(['guest']);
    }
    
    public function index()
    {
        return view('login');
    }

    public function login(UserLoginRequest $request)
    { 

       $url = $request->input('url');
  
       if (auth()->attempt($request->only('email', 'password')))
       {
            if ($url == route('login.store')) return redirect()->route('home');
            return redirect()->to($url);
       
       }
       
       return back()->with('error','email not found');

    }

    public function loginGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleProviderCallback()
    {    

            $provider = Socialite::driver('google')->stateless()->user();  
        
            if (!$provider) return redirect('/login');
            
            $user = User::where(['provider' => 'google', 'provider_id' => $provider->id])->first();

            if (!$user) {
                    $user = User::create([
                        'provider' => 'google',
                        'provider_id' => $provider->id,
                        'email' => $provider->email,
                        'name' => $provider->name,
                        'imagePath' => $provider->avatar,
                    ]);
            }          
           
            
    
            Auth::login($user);
    
            return  redirect('/');
        
    }


  
     

}
