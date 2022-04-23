<?php

namespace App\Http\Requests;

use App\Models\SocialSite;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSocialSiteRequest extends FormRequest
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
        $name_rules = 'required|unique:socail_sites';

        $site = SocialSite::where('name', $this->name)->first();
        if($site) $name_rules = 'required';      
      
        return [
            'name' => $name_rules,
            'url' => 'required',
        ];
    }
}
