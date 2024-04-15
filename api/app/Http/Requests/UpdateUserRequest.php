<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_name' => [
                'required',
                'string',
                'max:30',
            ],
            'current_password' => [
                'nullable',
                'required_with:new_password',
                'string',
                'current_password',
            ],
            'new_password' => [
                'nullable',
                'required_with:current_password',
                'string',
                'regex:/^[A-Za-z\d]{8,}$/',
            ],
        ];
    }
}
