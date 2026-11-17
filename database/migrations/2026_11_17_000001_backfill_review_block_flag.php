<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Reviews created before Review::$attributes['block'] existed were saved with block = NULL,
 * which the admin "unblocked" filter (block = 0) does not match.
 */
class BackfillReviewBlockFlag extends Migration
{
    public function up()
    {
        DB::table('reviews')->whereNull('block')->update(['block' => 0]);
    }

    public function down()
    {
        // Not reversible in a meaningful way; NULL and 0 both mean "not blocked".
    }
}
