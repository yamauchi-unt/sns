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

    /**
     * エラーメッセージのカスタマイズ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'required_with' => ':attributeを入力してください。',
            'max' => ':attributeは:max以内で入力してください。',
            'current_password' => ':attributeを正しく入力してください。',
            'new_password.regex' => ':attributeは半角英数字8文字以上で入力してください。',
        ];
    }

    /**
     * バリデーション属性のカスタマイズ
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'user_name' => 'ユーザ名',
            'current_password' => '現在のパスワード',
            'new_password' => '新しいパスワード',
        ];
    }
}
