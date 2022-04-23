<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShippingMethodRequest extends FormRequest
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
        $name_rules = 'required|unique:shipping_methods';
        if($this->_method)  
        {           
            if($this->_method == 'put' ) $name_rules  = 'required' ;
        }
        return [
            'name' =>  $name_rules,
            'description' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ];
    }
}
