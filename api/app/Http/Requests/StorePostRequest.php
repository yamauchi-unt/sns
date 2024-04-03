<?php

namespace App\Http\Requests;

use App\Rules\Base64FileType;
use App\Rules\Base64MaxSize;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前準備
     *
     * Base64エンコードされたimageをデコードし、新しいフィールドdecoded_imageとしてリクエストへ追加
     */
    protected function prepareForValidation()
    {
        if ($this->has('image')) {
            $encodeImage = $this->input('image');

            $pattern = '/^data:image\/[a-zA-Z]+;base64,/';
            $pureBase64Data = preg_replace($pattern, '', $encodeImage);
            $decodedImage = base64_decode($pureBase64Data);

            $this->merge([
                'decoded_image' => $decodedImage,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'decoded_image'=> [
                'required',
                new Base64MaxSize(5 * 1024 * 1024),
                new Base64FileType(['image/jpeg']),
            ],
            'message' => [
                'required',
                'string',
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
            'decoded_image.required' => ':attributeを選択してください。',
            'message.required' => ':attributeを入力してください。',
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
            'decoded_image' => '画像',
            'message' => '本文',
        ];
    }

}
