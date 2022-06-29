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



    public function index()
    {   
        return response()->json(UserShippingAddress::where('user_id',auth()->user()->id)->get());
    }

    public function show($id)
    {
        $address = UserShippingAddress::where(['user_id' => auth()->user()->id, 'id' => $id])->first();
        return response()->json($address);
    }

    public function default()
    {
        $address = UserShippingAddress::where(['user_id' => auth()->user()->id, 'status' => 1])->first();
        return response()->json($address);
    }

    public function store(ShippingAddressRequest $request)
    {  
        $address = $this->services->store($request, auth()->user()->id);
        return response()->json($address);
    }
 

    public function update(ShippingAddressRequest  $request, $id)
    {      
        $address =  UserShippingAddress::find($id);
        $this->services->update($request, $address);
        return response()->json($this->services->update($request, $address));     
    }

    public function destroy($id)
    {  
        UserShippingAddress::find($id)->delete();
        return response()->json(['success', 'Address successfully deleted']);          
    }

    public function setActive($id)
    {          
        $address = UserShippingAddress::find($id); 

        return response()->json([$this->services->set_default_address($address,auth()->user()->id)]); 
    }

}
