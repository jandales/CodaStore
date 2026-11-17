<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Payment step: the billing address (only required when it differs from shipping).
 * Card fields are gone: payment happens on PayPal.
 */
class BillingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        if ($this->input('is_new_billing') != 1) {
            return [];
        }

        return [
            'billing_firstname' => 'required|string|max:100',
            'billing_lastname' => 'required|string|max:100',
            'billing_street' => 'required|string|max:255',
            'billing_city' => 'required|string|max:100',
            'billing_phone' => 'required|string|max:30',
            'billing_country' => 'required|string|max:100',
            'billing_region' => 'required|string|max:100',
            'billing_zipcode' => 'required|string|max:20',
        ];
    }

    public function messages()
    {
        return ['required' => 'This field is required'];
    }
}
