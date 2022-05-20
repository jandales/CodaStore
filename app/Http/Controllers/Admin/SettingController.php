<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Services\Admin\SettingServices;
use App\Http\Requests\UpdateCampanyRequest;
use App\Http\Requests\UpdateGeneralSettingRequest;

class SettingController extends Controller
{
    private $services;

    public function __construct(SettingServices $services)
    {
        $this->services = $services;
    }
    public function index()
    {
        return  view('admin.settings');
    }

    public function general()
    {            
        $general_setting = GeneralSetting::find(1);
        return view('admin.setting.general')->with('general_setting', $general_setting);
    }

    public function social()
    {
        
        return view('admin.setting.social');
    }

    public function campany()
    {
        $general_setting = GeneralSetting::find(1);
        return view('admin.setting.campany')->with('general_setting', $general_setting);
    }

    public function emailer()
    {
        return view('admin.setting.emailer');
    }

    public function updateGeneral(UpdateGeneralSettingRequest $request, GeneralSetting $general_setting)
    {       
        $this->services->updateGeneral($request, $general_setting);   
        return  back()->with('success', 'Setting successfully Updated');      
    }

    public function UpdateCampanyAddress(UpdateCampanyRequest $request, GeneralSetting $general_setting)
    {            
     
        $this->services->UpdateCampanyAddres($request, $general_setting);   
      
        
        return back()->with('success', 'Setting successfully Updated');  
      
    }

    public function updateSocial(Request $request)
    {
        return $request->all();
    }

   

}
