<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Old reset tokens were the requester's CSRF token, so none of them can be trusted. */
class InvalidateOldPasswordResetTokens extends Migration
{
    public function up()
    {
        DB::table('password_resets')->delete();
        DB::table('admin_reset_password')->delete();
    }

    public function down()
    {
    }
}
