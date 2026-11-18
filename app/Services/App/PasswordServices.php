<?php

namespace App\Services\App;

use App\Mail\ForgotPasswordMail;
use App\Models\User;
use App\Services\Auth\ResetTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/** Customer "forgot password". */
class PasswordServices
{
    public const TABLE = 'password_resets';
    public const SENT = 'If an account exists for that email, we\'ve sent a link to reset your password. The link works for 60 minutes.';

    public function request($request)
    {
        // Same answer whether or not the email has an account, so nobody can check who shops here.
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $token = ResetTokens::issue(self::TABLE, $user->email);
            try {
                Mail::to($user->email)->queue(new ForgotPasswordMail(url("/reset_password/{$token}")));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['success' => self::SENT];
    }

    public function reset(Request $request)
    {
        $email = ResetTokens::email(self::TABLE, $request->token);
        $user = $email ? User::where('email', $email)->first() : null;
        if (!$user) {
            return ['status' => false, 'message' => 'This reset link has expired or was already used. Ask for a new one.'];
        }

        $user->password = Hash::make($request->password);
        $user->save();
        ResetTokens::forget(self::TABLE, $email);

        return ['status' => true, 'message' => 'Your password was changed. You can sign in now.'];
    }

    public function validateToken($token): bool
    {
        return ResetTokens::email(self::TABLE, $token) !== null;
    }
}
