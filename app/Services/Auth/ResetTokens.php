<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Password reset tokens for customers (password_resets) and staff (admin_reset_password).
 * Random, single-use, valid for 60 minutes, and only a hash is stored, so a database leak
 * doesn't hand out working reset links.
 */
class ResetTokens
{
    public const MINUTES = 60;

    /** A new token for this email (older ones stop working). Returns the token for the link. */
    public static function issue(string $table, string $email): string
    {
        $token = Str::random(64);

        DB::table($table)->where('email', $email)->delete();
        DB::table($table)->insert(['email' => $email, 'token' => hash('sha256', $token), 'created_at' => now()]);

        return $token;
    }

    /** The email a valid, unexpired token belongs to; null otherwise. */
    public static function email(string $table, $token): ?string
    {
        if (!is_string($token) || strlen($token) < 40) {
            return null;
        }

        $row = DB::table($table)
            ->where('token', hash('sha256', $token))
            ->where('created_at', '>=', now()->subMinutes(self::MINUTES))
            ->first();

        return $row ? $row->email : null;
    }

    /** Used up: remove every token for this email. */
    public static function forget(string $table, string $email): void
    {
        DB::table($table)->where('email', $email)->delete();
    }
}
