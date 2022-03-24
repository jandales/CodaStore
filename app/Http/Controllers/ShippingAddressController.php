<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingAddress;
use App\Http\Controllers\ShippingAddressController;

class ShippingAddressController extends Controller
{
    public function store(ShippingAddressController $request)
    {
        ShippingAddress::create([ 
        'firstname' => $request->firstname,
        'lastname' => $request->lasrname,
        'street' => $request->street,
        'city' => $request->city,
        'phone' => $request->phone,
        'country' => $request->country,
        'region' => $request->region,
        'zipcode' => $request->user,
        'user_id' => auth()->user()->id
        ]);
        return;
    }

    public function update(ShippingAddressController $request, ShippingAddress $address)
    {
        $address->firstname = $request->firstname;
        $address->lastname = $request->lasrname;
        $address->street = $request->street;
        $address->city = $request->city;
        $address->phone = $request->phone;
        $address->country = $request->country;
        $address->region = $request->region;
        $address->zipcode = $request->user;
        $address->save();
       
    }

    public function destroy( ShippingAddress $address)
    {
       
        $address->delete();
       
    }
}
