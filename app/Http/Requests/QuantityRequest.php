<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuantityRequest extends FormRequest
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
        // Products with variants (size, colour...) must send the chosen options.
        $product = $this->route('product');
        $needsOptions = $product && $product->hasVariants() > 0;

        return [
            'qty'                => 'required|integer|min:1',
            'properties'         => [$needsOptions ? 'required' : 'nullable', 'array'],
            'properties.*.name'  => 'required_with:properties|string',
            'properties.*.value' => 'required_with:properties|string',
        ];
    }

    public function messages()
    {
        // Same wording as the product page: "Please select size".
        $product = $this->route('product');
        $firstOption = optional(optional(optional($product)->attributes)->first())->attributes->name ?? 'your options';

        // Specific keys must come first: Laravel uses the first matching message.
        $messages = [];

        // Name the specific option that was left empty, e.g. properties.0 = size.
        foreach ((array) $this->input('properties', []) as $i => $item) {
            if (is_array($item) && !empty($item['name'])) {
                $messages["properties.$i.value.required_with"] = 'Please select ' . $item['name'];
            }
        }

        $messages['properties.required'] = 'Please select ' . $firstOption;
        $messages['properties.*.value.required_with'] = 'Please select your options';

        return $messages;
    }
}
