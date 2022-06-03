<?php
namespace App\Services;

use Illuminate\Http\Request;
use App\Models\UserShippingAddress;

class UserShippingServices
{
   
    public function store(Request $request)
    {
        $status = auth()->user()->shippingAddress->count() == 0 ? 1 : 0;
        $validated = $request->validated();
        $validated['user_id'] = auth()->user()->id;
        $validated['status'] = $status;
        return UserShippingAddress::create($validated); 
    }

    public function update(Request $request, UserShippingAddress $address)
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
        return $address;
    }

    public function set_default_address(UserShippingAddress $address)
    {
       
        $currentAddress = auth()->user()->shippingDefaultAddress();

        if(empty($currentAddress)) {
            Self::updateStatus($address, 1);
            return;
        }  

        Self::updateStatus($currentAddress, 0);
        Self::updateStatus($address, 1);      
        return;
        
    }

    private function updateStatus(UserShippingAddress $address, $status)
    {
        $address->status = (int)$status;
        $address->save();
        return $address;     
    }


}
