<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [           
            'reciept_name' => 'required|string',
            'reciept_number' => 'required|min:11|max:11',             
            'barangay' => 'required|string',
            'city_municipality' => 'required|string',
            'province' => 'required|string',
            'type' => 'required'  
        ];
    }
}
