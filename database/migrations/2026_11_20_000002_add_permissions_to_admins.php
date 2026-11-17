<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Staff access moves from a single role flag to named permissions. Administrators (role 1)
 * keep full access. Existing staff keep every area they could reach before, so nobody is
 * locked out on deploy; an administrator can then narrow them down in Staff users.
 */
class AddPermissionsToAdmins extends Migration
{
    private const ALL_STAFF = ['products', 'orders', 'refunds', 'customers', 'coupons', 'settings'];

    public function up()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->json('permissions')->nullable();
        });

        DB::table('admins')->where('role', '!=', 1)->update(['permissions' => json_encode(self::ALL_STAFF)]);
    }

    public function down()
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
}
