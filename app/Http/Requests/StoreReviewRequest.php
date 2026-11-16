<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            // review.update takes the rating from the URL, so only the form requires it.
            'rate'     => $this->routeIs('review.update') ? 'nullable' : 'required|integer|between:1,5',
            'comments' => 'required|string|max:1000',
        ];
    }

    public function messages()
    {
        return [
            'rate.required'     => 'Please select a rating',
            'rate.integer'      => 'Please select a rating',
            'rate.between'      => 'Please select a rating',
            'comments.required' => 'Please write your review',
            'comments.max'      => 'Please keep your review under 1,000 characters',
        ];
    }

    /**
     * Send the shopper back to the reviews section so the errors are visible.
     */
    protected function getRedirectUrl()
    {
        return strtok(url()->previous(), '#') . '#product-tabs';
    }
}
