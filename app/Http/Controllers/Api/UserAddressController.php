<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\UserShippingAddress;
use App\Http\Controllers\Controller;
use App\Services\UserShippingServices;
use App\Http\Requests\ShippingAddressRequest;

class UserAddressController extends Controller
{
    private $services;

    public function __construct(UserShippingServices $services)
    {
        $this->services = $services;
    }

    public function index($id)
    {   
        return response()->json([UserShippingAddress::all()]);
    }

    public function show($user_id, $id)
    {
        $address = UserShippingAddress::where(['user_id' => $user_id, 'id' => $id])->first();
        return response()->json($address);
    }

    public function store(ShippingAddressRequest $request, $user_id)
    {  
        return response()->json([$this->services->store($request,$user_id)]);
    }
 

    public function update(ShippingAddressRequest  $request,$user_id, $id)
    {      
        $address =  UserShippingAddress::find($address_id);
        $this->services->update($request, $address);
        return response()->json([$this->services->update($request, $address)]);     
    }

    public function destroy($user_id, $id)
    {  
        UserShippingAddress::find($address_id)->delete();
        return response()->json(['success', 'Address successfully deleted']);          
    }

    public function setActive($user_id, $id)
    {          
        $address = UserShippingAddress::find($address_id); 

        return response()->json([$this->services->set_default_address($address, $user_id)]); 
    }

}
