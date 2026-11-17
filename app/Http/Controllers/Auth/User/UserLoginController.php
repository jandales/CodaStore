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
        // Cancelling on Google's screen, an expired request or a Google outage all end up
        // here as an exception; send the customer back to sign in instead of an error page.
        try {
            $google = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('login')->with('error', "Google sign-in didn't finish. Please try again, or sign in with your email.");
        }

        if (!$google || !$google->getId() || !$google->getEmail()) {
            return redirect()->route('login')->with('error', "We couldn't get your email address from Google. Please sign in with your email instead.");
        }

        $googleId = (string) $google->getId();
        $email = mb_strtolower(trim($google->getEmail()));
        $emailVerified = (bool) data_get($google->user, 'email_verified', data_get($google->user, 'verified_email', false));

        $user = User::where('provider', 'google')->where('provider_id', $googleId)->first();

        if (!$user) {
            $existing = User::where('email', $email)->first();

            if ($existing) {
                // Same email as an existing account: link Google to it, but only when Google
                // confirms the address belongs to this person. Otherwise anyone could take
                // over an account by creating a Google login with someone else's email.
                if (!$emailVerified) {
                    return redirect()->route('login')->with('error', 'An account with this email already exists. Sign in with your email and password.');
                }

                $existing->forceFill([
                    'provider' => 'google',
                    'provider_id' => $googleId,
                    'imagePath' => $existing->imagePath ?: $google->getAvatar(),
                    'email_verified_at' => $existing->email_verified_at ?: now(),
                ])->save();
                $user = $existing;
            } else {
                $user = User::create([
                    'provider' => 'google',
                    'provider_id' => $googleId,
                    'email' => $email,
                    'name' => $google->getName() ?: strstr($email, '@', true),
                ]);
                $user->forceFill([
                    'imagePath' => $google->getAvatar(),
                    // Google has already confirmed this address.
                    'email_verified_at' => $emailVerified ? now() : null,
                ])->save();
            }
        }

        Auth::login($user, true);

        // Back to where they were going (e.g. checkout), or the home page.
        return redirect()->to(self::customerIntendedUrl());
    }

    /** The page a customer was heading to before signing in, never an admin page. */
    private static function customerIntendedUrl(): string
    {
        $url = session()->pull('url.intended');
        $path = $url ? '/' . ltrim((string) parse_url($url, PHP_URL_PATH), '/') : null;

        if (!$url || $path === '/admin' || str_starts_with($path, '/admin/')) {
            return route('home');
        }

        return $url;
    }

}
