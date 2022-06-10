<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\CustomerServices;

class UserController extends Controller
{
   private $services;

   public function __construct(CustomerServices $services)
   {
      $this->services = $services;
   }
   
   public function index()
   {
      $users = $this->services->list();
      
      return view('admin.customers.index')->with('users', $users);
   }

   public function search(Request $request)
   {     
      $this->services->search($request);
      return view('admin.customers.index')->with(['users' =>  $users, 'keyword' => $request->keyword]);
   }

   public function destroy(User $user)
   {  
      $this->authorize('delete', $user); 

      $this->services->destroy($user);  

      return back()->with('success', 'User successfully deleted');
   }

   public function selectedDestroy(Request $request)
   {   
      $user = User::find($request->selected[0]);
      
      $this->authorize('delete', $user);

      $this->services->deleteSelectedItem($request->selected);

      return back()->with('success', 'User successfully deleted');
   }

   public function show(User $user)
   {           
      return view('admin.customers.show')->with('user', $user);
   }

   public function edit()
   {
      return view('account.edit');
   }

   public function account()
   {       
      return view('account.profile');
   }

   public function password()
   {
      return view('account.password');
   }

   public function upload()
   {
      return view('account.upload');
   }

   public function update(Request $request)
   { 
      $this->services->update($request); 
      return redirect()->route('account')->with('status','Profile updated successfully');
   }  

   public function changePassword(UserRequest $request)
   { 
      $this->services->changePassword($request);   
      return back()->with('success','Password Successfully Changed');
   }

   public function avatar(Request $request, CustomerServices $service)
   {     
      $result = $service->updateAvatar($request);
      return  back()->with($result);
   }
   
}

