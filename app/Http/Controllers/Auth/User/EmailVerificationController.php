<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /** "Check your inbox" page; checkout sends unverified customers here. */
    public function notice(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('account'));
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect()->intended(route('account'))->with('success', 'Thanks, your email is confirmed.');
    }

    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('account'));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'We sent a new link to ' . $request->user()->email . '.');
    }
}
