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
                'short_description' => 'string',
                'imagePath' => 'string',
                'long_description' => 'string',
                'sku' => 'string',   
                'barcode' => 'string',     
                'tags' => 'string',
                'sale_price' => 'numeric',
                'regular_price' => 'numeric',
                'status' => 'numeric',
                'is_taxable' => 'numeric',
                'featured' => 'numeric',
             
        ];
    }
}
