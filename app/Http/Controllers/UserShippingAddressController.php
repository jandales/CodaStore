<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShippingAddress;
use App\Services\UserShippingServices;
use App\Http\Requests\ShippingAddressRequest;


class UserShippingAddressController extends Controller
{
    private $services;

    public function __construct(UserShippingServices $services)
    {
        $this->services = $services;
    }
    public function index()
    {    
        return view('account.shippingaddress.index')->with('shippingaddress', auth()->user()->shippingAddress);
    }

    public function create()
    {
        return view('account.shippingaddress.create');
    }

    public function store(ShippingAddressRequest $request)
    {      
        $this->services->store($request);  
        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully created');
    }

    public function edit(UserShippingAddress $address)
    {
        return view('account.shippingaddress.edit')->with('address', $address);
    }

    public function update(ShippingAddressRequest  $request, UserShippingAddress $address)
    {       
        $this->services->update($request, $address);
        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully updated');       
    }

    public function destroy(UserShippingAddress $address)
    {      
        $address->delete();
        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully deleted');       
    }

    public function set_default_address(UserShippingAddress $address)
    {   
        $this->services->set_default_address($address);
        return back()->with('success', 'Address successfully updated'); 
    }



}
