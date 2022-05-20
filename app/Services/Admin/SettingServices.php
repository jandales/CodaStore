<?php 
namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\GeneralSetting;

class SettingServices
{
    public function updateGeneral(Request $request, GeneralSetting $general_setting)
    {       
     
        if (GeneralSetting::get()->count() == 0)
        {                   
            $general_setting = GeneralSetting::create([
                'site_name' => $request->site_name,
                'tag_line' => $request->tag_line,
                'site_email' => $request->site_email,
                'site_url' => $request->site_url,
                'timezone' => $request->timezone,
                'date_format' => $request->date_format,
                'time_format' => $request->time_format,
            ]);
            return;
        }  

        $general_setting->site_name = $request->site_name;
        $general_setting->tag_line = $request->tag_line;
        $general_setting->site_url = $request->site_url;
        $general_setting->site_email = $request->site_email;
        $general_setting->timezone = $request->timezone;
        $general_setting->date_format = $request->date_format;
        $general_setting->time_format = $request->time_format;
        $general_setting->save(); 
    }

    public function UpdateCampanyAddress(Request $request, GeneralSetting $general_setting)
    {          
        $general_setting->campany_name = $request->name;
        $general_setting->campany_address = $request->address;
        $general_setting->campany_city = $request->city;
        $general_setting->campany_region = $request->region;
        $general_setting->campany_country = $request->country;
        $general_setting->campany_zipcode = $request->zipcode;
        $general_setting->campany_phone = $request->phone;       
        $general_setting->save();      
    }
}