<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StorePaymentOptionRequest;

class PaymentOptionController extends Controller
{
    public function store(StorePaymentOptionRequest $request)
    {
            PaymentOption::create([
                'card_name' => $request->card_name,
                'card_number' => $request->card_number,
                'cart_expired_date' => $request->expired_date,
                'card_cvc' => $request->cvc,
                'user_id' => auth()->user()->id
            ]);

            return;
    }

    public function update(StorePaymentOptionRequest $request,PaymentOption $payment)
    {
    
            $payment->card_name = $request->card_name;
            $payment->card_number = $request->card_number;
            $payment->cart_expired_date = $request->expired_date;
            $payment->card_cvc = $request->cvc;
            $payment->save();

        
    }

    public function destroy(PaymentOption $payment)
    {
         return $payment->delete();   
    }
}
