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
            "user_id"=> [
                "required",
                "string",
                "max:30",
                Rule::unique("users")->ignore($this->user_id),
                'regex:/^[A-Za-z0-9_]+$/',
            ],
            'user_name'=> [
                'required',
                'string',
                'max:30',
            ],
            'password'=> [
                'required',
                'string',
                'regex:/^[A-Za-z\d]{8,}$/'
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
            'max' => ':attributeは:max以内で入力してください。',
            'user_id.unique' => '既に登録されています。異なる:attributeを入力してください。',
            'user_id.regex' => ':attributeは半角英数字_(ｱﾝﾀﾞｰｽｺｱ)のみで入力してください。',
            'password.regex' => ':attributeは半角英数字8文字以上で入力してください。',
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
            'user_id' => 'ユーザID',
            'user_name' => 'ユーザ名',
            'password'=> 'パスワード',
        ];
    }
}
