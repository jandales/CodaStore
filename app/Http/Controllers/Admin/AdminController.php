<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Services\UsersServices;
use App\Http\Requests\AdminRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UpdataPasswordRequest;


class AdminController extends Controller
{
   
    private $services;

    public function  __construct(UsersServices $services)
    {
        $this->authorizeResource(Admin::class, 'admin');

        $this->services = $services;
    }

    public function index()
    { 
        return view('admin.users.index')->with('users', $this->services->users());
    }

    public function create()
    {      
        return view('admin.users.create');
    }

    public function store(AdminRequest $request)
    {   
        $this->services->store($request);

        return redirect()->route('admin.users')->with('success', 'User successfully create');
    }

    public function edit(Admin $admin)
    {       
        return view('admin.users.edit')->with('user', $admin);
    }

    public function update(Request $request, Admin $admin)
    {               

        $this->services->update($request, $admin); 

        return back()->with('success', 'User successfully updated'); 
    }

    public function show(Admin $admin)
    {
        return view('admin.users.show')->with('user', $admin);
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();

        return back()->with('success', 'User successfully Deleted'); 
    }

    public function destroySelectedItem(Request $request)
    {        
        $this->services->destroy($request);

        return back()->with('success', 'User successfully Deleted'); 
    }

    public function updateSelectItemRoleTo(Request $request)
    {
        $res = $this->services->updateRoleTo($request); 

        return back()->with($res['status'], $res['message']); 
    }

    public function search(Request $request)
    {        
        $users = Admin::Search($request->keyword)->get()->except(auth()->guard('admin')->user()->id);
        return view('admin.users.index')->with(['users' => $users, 'keyword' => $request->keyword]);
    }    

    public function sentResetPassword(Admin $admin)
    {
        return $admin;
    } 

    public function profile()
    {
        return view('admin.account.index')->with('user', auth()->guard('admin')->user());        
    }

    public function updatePassword(UpdataPasswordRequest $request, Admin $admin)
    {   
        $admin->password = Hash::make($request->password);
        $admin->save();
        return response()->json(['message' => 'Password Update']);        
    }
    
}
