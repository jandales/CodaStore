<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentOptionRequest extends FormRequest
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
            'card_name' => 'required',
            'card_number'  => "required|min:21|unique:payment_options,card_number,{$this->option->id}",
            'card_expire_date' => 'required',
            'card_cvc' => 'required|min:3',    
        ];
    }
}
