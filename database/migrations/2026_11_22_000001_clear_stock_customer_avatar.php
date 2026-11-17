<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * New customers used to get /img/avatar/default-avatar.jpg, a stock photo of a person, as
 * their profile picture. Customers who never uploaded a photo now have none, and the site
 * shows a neutral placeholder (User::avatar()).
 */
class ClearStockCustomerAvatar extends Migration
{
    private const STOCK = '/img/avatar/default-avatar.jpg';

    public function up()
    {
        DB::table('users')->where('imagePath', self::STOCK)->update(['imagePath' => null]);
    }

    public function down()
    {
        DB::table('users')->whereNull('imagePath')->update(['imagePath' => self::STOCK]);
    }
}
