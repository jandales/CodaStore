<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Couriers move from config/couriers.php to the database so the store can manage them in the
 * admin. The existing couriers keep their codes, which orders.courier already stores.
 */
class CreateCouriersTable extends Migration
{
    public function up()
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name', 80);
            $table->string('tracking_url', 500)->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        $couriers = [
            'lbc' => 'LBC Express',
            'jnt' => 'J&T Express',
            'ninjavan' => 'Ninja Van',
            'flash' => 'Flash Express',
            '2go' => '2GO Express',
            'grab' => 'Grab Express',
            'lalamove' => 'Lalamove',
            'other' => 'Other courier',
        ];

        $sort = 0;
        foreach ($couriers as $code => $name) {
            DB::table('couriers')->insert([
                'code' => $code,
                'name' => $name,
                'tracking_url' => null,
                'active' => true,
                'sort_order' => $sort += 10,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('couriers');
    }
}
