<?php

namespace App\Http\Controllers\Admin;


use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCampanyRequest;
use App\Http\Requests\UpdateGeneralSettingRequest;

class SettingController extends Controller
{
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
     
        if (GeneralSetting::get()->count() == 0){                   
            $general_setting = GeneralSetting::create([
                'site_name' => $request->site_name,
                'tag_line' => $request->tag_line,
                'site_email' => $request->site_email,
                'site_url' => $request->site_url,
                'timezone' => $request->timezone,
                'date_format' => $request->date_format,
                'time_format' => $request->time_format,
            ]);
            return  back()->with('success', 'Setting successfully Updated');
        }  

        $general_setting->site_name = $request->site_name;
        $general_setting->tag_line = $request->tag_line;
        $general_setting->site_url = $request->site_url;
        $general_setting->site_email = $request->site_email;
        $general_setting->timezone = $request->timezone;
        $general_setting->date_format = $request->date_format;
        $general_setting->time_format = $request->time_format;
        $general_setting->save();
        
        return back()->with('success', 'Setting successfully Updated');


        
       

        
      
    }

    public function UpdateCampanyAddress(UpdateCampanyRequest $request, GeneralSetting $general_setting)
    {            
     
        $general_setting->campany_name = $request->name;
        $general_setting->campany_address = $request->address;
        $general_setting->campany_city = $request->city;
        $general_setting->campany_region = $request->region;
        $general_setting->campany_country = $request->country;
        $general_setting->campany_zipcode = $request->zipcode;
        $general_setting->campany_phone = $request->phone;       
        $general_setting->save();
        
        return back()->with('success', 'Setting successfully Updated');  
      
    }

    public function updateSocial(Request $request)
    {
        return $request->all();
    }

   

}
