<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
        
        $rules['card_name'] = 'required';
        $rules['card_number'] = 'required|min:16';
        $rules['card_expire_date'] = 'required:max:5';
        $rules['card_cvc'] = 'required:numeric';

        if ($this->is_new_billing != 1) return $rules;
               
        $rules['billing_firstname'] = 'required';
        $rules['billing_lastname'] = 'required';
        $rules['billing_street'] = 'required';
        $rules['billing_city'] = 'required';
        $rules['billing_phone'] = 'required';
        $rules['billing_country'] = 'required';
        $rules['billing_region'] = 'required';
        $rules['billing_zipcode'] = 'required';         
        return $rules; 
      
    }
}
