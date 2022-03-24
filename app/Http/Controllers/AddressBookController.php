<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use Illuminate\Http\Request;
use App\Http\Requests\AddressBookRequest;

class AddressBookController extends Controller
{
    public function index()
    {
        $addressBooks = auth()->user()->addressBooks;
        return view('account.addressbook')->with('addressBooks',$addressBooks);        
    }

    public function create()
    {
        return view('account.addressbook.create');
    }

    public function edit(AddressBook $addressbook)
    {
        return view('account.addressbook.edit')->with('addressbook', $addressbook);
    }

    public function store(AddressBookRequest $request)
    {   
       
        AddressBook::create([
            'user_id' => auth()->user()->id,
            'reciept_name' => $request->reciept_name,
            'reciept_email' => $request->email,
            'reciept_number' => $request->reciept_number,            
            'street'=> $request->street,          
            'barangay'=> $request->barangay,
            'city_municipality'=> $request->city_municipality,
            'province'=> $request->province,
            'type' => $request->type,  
            'status' =>  auth()->user()->addressBooks->count() == 0 ? 1 : 0, 
        ]);
        
        return redirect()->route('account.addressbook')->with('success', 'address successfully created');
    }


   
    public function update(AddressBookRequest $request, AddressBook $addressbook)
    {

        $addressbook->reciept_name  = $request->reciept_name;
        $addressbook->reciept_number = $request->reciept_number;
        $addressbook->street = $request->street;
        $addressbook->barangay = $request->barangay;
        $addressbook->city_municipality = $request->city_municipality;
        $addressbook->province = $request->province;
        $addressbook->type = $request->type;
        $addressbook->save();

        return redirect()->route('account.addressbook')->with('success', 'address successfully updated');

    }

    public function default(AddressBook $addressbook)
    {          
       $this->setToDefault($addressbook);
       return back()->with('success', 'Successfully set to default address');
    }   

    public function destroy(AddressBook $addressbook)
    {  
        $addressbook->delete();
        return back()->with('success', 'Successfully deleted');
    }

    private function setToDefault(AddressBook $addressbook)
    {        
        $currentDefaultAddress = auth()->user()->defaultAddress(); 
        if(empty($currentDefaultAddress)) return  $this->updateStatus($addressbook, 1);
        if($addressbook->id == $currentDefaultAddress->id)  return;
        $this->updateStatus($currentDefaultAddress, 0);
        $this->updateStatus($addressbook, 1);
    
    }

    public function updateStatus(AddressBook $addressbook, $status)
    {   
        $addressbook->status = $status;
        $addressbook->save();
        return $addressbook;
    }

    /*--AJAX request Controller -- */

    public function ajaxSetAddress(AddressBook $addressbook)
    {
       $this->setToDefault($addressbook);

        return response()->json(['status' => 'success']);  

    }

    
}


