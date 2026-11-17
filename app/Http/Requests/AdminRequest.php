<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminRequest extends FormRequest
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
            'username' => 'required|unique:admins',
            'email' => 'required|unique:admins|email',
            'role' => ['required', Rule::in([Admin::ROLE_ADMINISTRATOR, Admin::ROLE_STAFF])],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(array_keys(Admin::PERMISSIONS))],
            'firstname' => 'string',
            'lastname' => 'string',
            'password' => 'required|min:8',
        ];
    }
}
