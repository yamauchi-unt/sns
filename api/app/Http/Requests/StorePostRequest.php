<?php

namespace App\Http\Requests;

use App\Rules\ImageFileType;
use App\Rules\ImageMaxSize;
use App\Services\ImageService;
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
     * imageをデコード
     **/
    protected function prepareForValidation()
    {
        if ($this->has('image')) {
            $encodedImage = $this->input('image');

            $decodedImage = ImageService::decodeBase64($encodedImage);

            $this->merge([
                'image' => $decodedImage,
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
            'image'=> [
                'required',
                new ImageMaxSize(env('IMAGE_MAX_SIZE', '5242880')),
                new ImageFileType(env('IMAGE_ALLOWED_MIME_TYPE', 'image/jpeg')),
            ],
            'message' => [
                'required',
            ],
        ];
    }
}
