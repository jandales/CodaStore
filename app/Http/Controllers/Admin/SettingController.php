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

    public function payments()
    {
        return view('admin.setting.payments', [
            'settings' => GeneralSetting::firstOrFail(),
            'shippingMethods' => \App\Models\ShippingMethod::orderBy('name')->get(),
        ]);
    }

    public function updatePayments(Request $request)
    {
        $data = $request->validate([
            'cod_fee' => ['required', 'numeric', 'min:0', 'max:10000'],
            'cod_max_total' => ['nullable', 'numeric', 'min:0'],
            'cod_max_open_orders' => ['required', 'integer', 'min:0', 'max:50'],
            'cod_shipping_methods' => ['nullable', 'array'],
            'cod_shipping_methods.*' => ['integer', 'exists:shipping_methods,id'],
        ], [
            'cod_fee.required' => 'Enter a COD fee, or 0 for none.',
        ]);

        GeneralSetting::firstOrFail()->update([
            'cod_enabled' => $request->boolean('cod_enabled'),
            'cod_fee' => round((float) $data['cod_fee'], 2),
            'cod_max_total' => ($data['cod_max_total'] ?? null) !== null && (float) $data['cod_max_total'] > 0 ? round((float) $data['cod_max_total'], 2) : null,
            'cod_max_open_orders' => (int) $data['cod_max_open_orders'],
            'cod_shipping_methods' => array_values(array_map('intval', $data['cod_shipping_methods'] ?? [])) ?: null,
        ]);

        return back()->with('success', 'Payment settings saved.');
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
