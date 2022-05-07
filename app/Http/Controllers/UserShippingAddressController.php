<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserShippingAddress;
use App\Http\Requests\ShippingAddressRequest;


class UserShippingAddressController extends Controller
{
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
        $status = auth()->user()->shippingAddress->count() == 0 ? 1 : 0;
        UserShippingAddress::create([ 
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'street' => $request->street,
            'city' => $request->city,
            'phone' => $request->phone,
            'country' => $request->country,
            'region' => $request->region,
            'zipcode' => $request->zipcode,
            'user_id' => auth()->user()->id,
            'status' => $status,
        ]);      
        
        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully created');
    }

    public function edit(UserShippingAddress $address)
    {
        return view('account.shippingaddress.edit')->with('address', $address);
    }

    public function update(ShippingAddressRequest  $request, UserShippingAddress $address)
    {
        $address->firstname = $request->firstname;
        $address->lastname = $request->lastname;
        $address->street = $request->street;
        $address->city = $request->city;
        $address->phone = $request->phone;
        $address->country = $request->country;
        $address->region = $request->region;
        $address->zipcode = $request->zipcode;
        $address->save();

        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully updated');
       
    }

    public function destroy(UserShippingAddress $address)
    {      
        $address->delete();
        return redirect()->route('account.shippingaddress')->with('success', 'Address successfully deleted');       
    }

    public function set_default_address(UserShippingAddress $address)
    {
       
        $currentAddress = auth()->user()->shippingDefaultAddress();

        if(empty($currentAddress)) {
            $this->updateStatus($address, 1);
            return back()->with('success', 'Address successfully updated');  
        }  

        $this->updateStatus($currentAddress, 0);
        $this->updateStatus($address, 1);
      
        return back()->with('success', 'Address successfully updated');  
        
    }

    public function updateStatus(UserShippingAddress $address, $status)
    {
        $address->status = (int)$status;
        $address->save();
        return $address;     
    }

}
