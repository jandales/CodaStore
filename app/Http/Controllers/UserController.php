<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Services\CustomerServices;

class UserController extends Controller
{
   public function index()
   {
      $users = User::where('delete_at', 0)->paginate(10);
      return view('admin.customers.index')->with('users', $users);
   }

   public function search(Request $request)
   {     
      $users = User::Search($request->keyword)->paginate(10);
      return view('admin.customers.index')->with(['users' =>  $users, 'keyword' => $request->keyword]);
   }

   public function destroy(User $user)
   {         
      $user->delete_at = 1;
      $user->save();
      return back()->with('success', 'User successfully deleted');
   }

   public function selectedDestroy(Request $request, CustomerServices $service)
   {   
      $service->deleteSelectedItem($request->selected);
      return back()->with('success', 'User successfully deleted');
   }

   public function edit()
   {
      return view('account.edit');
   }

   public function account(){
       
      return view('account.profile');
   }

   public function password(){
      return view('account.password');
   }

   public function upload(){
      return view('account.upload');
   }

   public function update(Request $request, CustomerServices $service)
   { 
      $service->update($request); 
      return redirect()->route('account')->with('status','Profile updated successfully');
   }

   public function show(User $user)
   {   
      // $user = User::where('id',$id)->with('orders')->first();      
      return view('admin.customers.show')->with('user', $user);
   }

   public function changePassword(UserRequest $request, CustomerServices $service)
   { 
      $service->changePassword($request);   
      return back()->with('success','Password Successfully Changed');
   }

   public function requestResetPassword(Request $request)
   {
      $tokens = [$request->_token, session()->token()];  
   }

   public function avatar(Request $request, CustomerServices $service)
   {     
     $result = $service->updateAvatar($request);
     if(!$result) return back()->with(['error' => 'Please select image to upload']);
     return  back()->with(['success' => 'Image Successfully upload']);
   }
   
}

