<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\UserShippingAddress;

class UserShippingServices
{
    public function store(Request $request, $user_id)
    {
       
        $user = User::find($user_id);
        $status = $user->shippingAddress->count() == 0 ? 1 : 0;
        $validated = $request->validated();
        $validated['user_id'] = $user_id;
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

    public function set_default_address(UserShippingAddress $address, $user_id)
    {
        
        $currentAddress = UserShippingAddress::where(['user_id' => $user_id, 'status' => 1])->first();

        if(empty($currentAddress)) {            
            return Self::updateStatus($address, 1);
        }  

        Self::updateStatus($currentAddress, 0);
        $address = Self::updateStatus($address, 1);      
        return $address;        
    }

    private function updateStatus(UserShippingAddress $address, $status)
    {
        $address->status = (int)$status;
        $address->save();
        return $address;     
    }


}
