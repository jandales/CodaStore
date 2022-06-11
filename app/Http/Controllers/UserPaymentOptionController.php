<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserPaymentOption;
use App\Services\UserPaymentOptionServices;
use App\Http\Requests\StorePaymentOptionRequest;
use App\Http\Requests\UpdatePaymentOptionRequest;

class UserPaymentOptionController extends Controller
{
    private $services;

    public function __construct(UserPaymentOptionServices $services)
    {
        $this->services = $services;
    }
    
    public function index()
    {   
        $this->authorize('viewAny', UserPaymentOption::class); 

        return view('account.payment.index')->with('payment_options', auth()->user()->payment_options);
    }

    public function create()
    {
        $this->authorize('create', UserPaymentOption::class); 

        return view('account.payment.create');
    }

    public function edit(UserPaymentOption $option)
    {
        $this->authorize('update', $option);

        return view('account.payment.edit')->with('option', $option);
    }    
    
    public function store(StorePaymentOptionRequest $request)
    {   
        $this->authorize('create', UserPaymentOption::class); 

        $this->services->store($request);        

        return redirect()->route('account.payment-option')->with('success', 'Card Successfully Addedd');
    }

    public function update(UpdatePaymentOptionRequest $request,UserPaymentOption $option)
    {          
        $this->authorize('update', $option);

        $this->services->update($request, $option);

        return redirect()->route('account.payment-option')->with('success', 'Card Successfully updated');        
    }

    public function destroy(UserPaymentOption $option)
    {         
        $this->authorize('delete', $option);

        $option->delete();  

        return redirect()->route('account.payment-option')->with('success', 'Card Successfully updated');
    }

    public function updateStatus(UserPaymentOption $option)
    {  
        $this->authorize('update', $option);

        $result = $this->services->updateStatus($option);

        return back()->with($result);
    }
   
}
