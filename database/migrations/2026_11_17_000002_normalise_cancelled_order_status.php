<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Customer cancellations used to be saved as "canceled" (one L) while the rest of the app
 * uses "cancelled", so those orders never showed up as cancelled.
 */
class NormaliseCancelledOrderStatus extends Migration
{
    public function up()
    {
        DB::table('orders')->where('status', 'canceled')->update(['status' => 'cancelled']);
    }

    public function down()
    {
        // The old spelling was a bug; nothing to restore.
    }
}
