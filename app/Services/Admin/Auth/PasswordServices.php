<?php

namespace App\Services\Admin\Auth;

use App\Mail\AdminResetPassword;
use App\Models\Admin;
use App\Services\Auth\ResetTokens;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/** Staff "forgot password". */
class PasswordServices
{
    public const TABLE = 'admin_reset_password';
    public const SENT = 'If that email belongs to a staff account, we\'ve sent a link to reset the password. The link works for 60 minutes.';

    public function request(Request $request)
    {
        $admin = Admin::where('email', $request->email)->first();
        if ($admin) {
            $token = ResetTokens::issue(self::TABLE, $admin->email);
            try {
                Mail::to($admin->email)->queue(new AdminResetPassword(url("/admin/reset_password/{$token}")));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return ['success' => self::SENT];
    }

    public function store(Request $request)
    {
        $email = ResetTokens::email(self::TABLE, $request->token);
        $admin = $email ? Admin::where('email', $email)->first() : null;
        if (!$admin) {
            return ['error' => 'This reset link has expired or was already used. Ask for a new one.'];
        }

        $admin->password = Hash::make($request->password);
        $admin->save();
        ResetTokens::forget(self::TABLE, $email);

        return ['success' => 'Password changed. You can sign in now.'];
    }

    public function validateToken($token): bool
    {
        return ResetTokens::email(self::TABLE, $token) !== null;
    }
}
