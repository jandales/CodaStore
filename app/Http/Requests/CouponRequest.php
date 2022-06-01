<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponRequest extends FormRequest
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
            'name' => "required|unique:coupons",          
            'discount_type' => 'required',
            'amount' => 'required|numeric',
            'expire_at' => 'required|date|after_or_equal:today',            
            'min_amount' => 'numeric',
            'max_amount' => 'numeric',
            'limit_per_coupon' => 'numeric',
            'limit_to_xitems' => 'numeric',
            'limit_per_user' => 'numeric',
            'expire_at' => 'date'
        ];
    }
}
