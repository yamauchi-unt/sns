<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
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
            'user_id' => [
                'bail',
                'required',
                'string',
                'max:30',
                'unique:users,user_id',
                'regex:/^[A-Za-z0-9_]+$/',
            ],
            'user_name' => [
                'bail',
                'required',
                'string',
                'max:30',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'regex:/^[A-Za-z\d]{8,}$/'
            ],
        ];
    }
}
