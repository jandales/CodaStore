<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Card numbers and CVCs used to be stored in plain text:
 *  - user_payment_options held saved cards (number, expiry, CVC);
 *  - order_payments.reference_number held the full card number for "card" orders.
 * Payments now go through PayPal, so this data is removed. Back up the database before
 * running this in production if you need an audit copy (store it encrypted, offline).
 */
class RemoveStoredCardData extends Migration
{
    public function up()
    {
        // Keep only the last four digits on old card payments, for order history.
        DB::table('order_payments')->where('provider', 'card')->orderBy('id')->chunkById(200, function ($payments) {
            foreach ($payments as $payment) {
                $digits = preg_replace('/\D/', '', (string) $payment->reference_number);
                DB::table('order_payments')->where('id', $payment->id)->update([
                    'reference_number' => $digits === '' ? 'removed' : 'card ending ' . substr($digits, -4),
                ]);
            }
        });

        Schema::dropIfExists('user_payment_options');
    }

    public function down()
    {
        // The table comes back empty; removed card data cannot (and should not) be restored.
        Schema::create('user_payment_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('card_name');
            $table->string('card_number');
            $table->string('card_expire_date');
            $table->string('card_cvc');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }
}
