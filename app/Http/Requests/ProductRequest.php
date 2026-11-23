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
                // Optional: a product can be saved without them (an empty price is saved as 0).
                'sku' => 'nullable|string|max:255',
                'barcode' => 'nullable|string|max:255',
                'regular_price' => 'nullable|numeric|min:0',
                'on_sale' => 'nullable|boolean',
                // Optional; required and below the regular price only when the product is on sale.
                'sale_price' => 'nullable|numeric|min:0|required_if:on_sale,1' . ($this->input('on_sale') ? '|lt:regular_price' : ''),
                'status' => 'numeric',
                'is_taxable' => 'numeric',
                'featured' => 'numeric',
                // Variants (one row per option combination) when "This product has multiple options" is ticked.
                'attributes' => ['nullable', 'array', 'max:' . \App\Services\Products\ProductSkus::MAX_OPTIONS],
                'skus' => [$this->hasVariants() ? 'required' : 'nullable', 'array', 'max:' . \App\Services\Products\ProductSkus::MAX_VARIANTS],
                'skus.*.options' => ['required', 'string', 'max:1000'],
                'skus.*.qty' => ['required', 'integer', 'min:0', 'max:1000000'],
                'skus.*.price' => ['nullable', 'numeric', 'min:1'],
                'skus.*.image' => ['nullable', 'string', 'max:500'],
                'skus.*.sku' => ['nullable', 'string', 'max:100'],
                'skus.*.barcode' => ['nullable', 'string', 'max:100'],

        ];
    }

    /** "This product has multiple options" is ticked and at least one option was added. */
    public function hasVariants(): bool
    {
        return $this->boolean('hasVariant') && !empty($this->input('attributes'));
    }

    public function messages()
    {
        return [
            'sale_price.required_if' => 'Enter a sale price, or untick "On sale".',
            'sale_price.lt' => 'The sale price must be lower than the regular price.',
            'attributes.max' => 'A product can have at most ' . \App\Services\Products\ProductSkus::MAX_OPTIONS . ' options (for example Size and Colour).',
            'skus.required' => 'Add at least one option value, or untick "This product has multiple options".',
            'skus.max' => 'That makes too many variants. Use fewer option values (at most ' . \App\Services\Products\ProductSkus::MAX_VARIANTS . ' combinations).',
            'skus.*.qty.required' => 'Enter how many are available for every variant (0 is fine).',
            'skus.*.qty.min' => 'Available can\'t be negative.',
            'skus.*.price.min' => 'Variant prices must be at least 1.',
        ];
    }
}
