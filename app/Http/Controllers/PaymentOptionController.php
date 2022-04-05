<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentOption;
use App\Services\PaymentOptionServices;
use App\Http\Requests\StorePaymentOptionRequest;
use App\Http\Requests\UpdatePaymentOptionRequest;

class PaymentOptionController extends Controller
{
    private $services;

    public function __construct(PaymentOptionServices $services)
    {
        $this->services = $services;
    }
    
    public function index()
    {        
        return view('account.payment.index')->with('payment_options', auth()->user()->payment_options);
    }

    public function create()
    {
        return view('account.payment.create');
    }

    public function edit(PaymentOption $option)
    {
        return view('account.payment.edit')->with('option', $option);
    }    
    
    public function store(StorePaymentOptionRequest $request)
    {      
        $this->services->store($request);
        return redirect()->route('account.payment-option')->with('success', 'Card Successfully Addedd');
    }

    public function update(UpdatePaymentOptionRequest $request,PaymentOption $option)
    {          
        $this->services->update($request, $option);
        return redirect()->route('account.payment-option')->with('success', 'Card Successfully updated');        
    }

    public function destroy(PaymentOption $option)
    {         
         $option->delete();  
         return redirect()->route('account.payment-option')->with('success', 'Card Successfully updated');
    }

    public function updateStatus(PaymentOption $option)
    {         
        if (!$this->services->updateStatus($option)) return redirect()->route('account.payment-option')->with('success', 'Card Already set default payment method');    

        return redirect()->route('account.payment-option')->with('success', 'Card Successfully updated');
    }
   
}
