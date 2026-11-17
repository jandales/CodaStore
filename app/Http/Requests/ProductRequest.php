<?php

namespace App\Http\Requests;

use App\Rules\SelectValue;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
                'name' => 'required',                
                'category_id' => ['required', new SelectValue],  
                'slug' => 'string',                 
                'sku' => 'required|string',   
                'barcode' => 'required|string', 
                'regular_price' => 'required|numeric|min:1',
                'on_sale' => 'nullable|boolean',
                // Optional; required and below the regular price only when the product is on sale.
                'sale_price' => 'nullable|numeric|min:0|required_if:on_sale,1' . ($this->input('on_sale') ? '|lt:regular_price' : ''),
                'status' => 'numeric',
                'is_taxable' => 'numeric',
                'featured' => 'numeric',
             
        ];
    }

    public function messages()
    {
        return [
            'sale_price.required_if' => 'Enter a sale price, or untick "On sale".',
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
        ];
    }
}
