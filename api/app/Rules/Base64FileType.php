<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64FileType implements Rule
{
    protected $allowedMimeType;

    /**
     * @param string $allowedMimeType
     */
    public function __construct(string $allowedMimeType)
    {
        $this->allowedMimeType = $allowedMimeType;
    }

    /**
     * 許可されたファイル形式かチェックする
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // 画像データのMIMEタイプを取得
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $value);
        finfo_close($finfo);

        return $mimeType === $this->allowedMimeType;
    }

    /**
     * エラーメッセージ
     *
     * @return string
     */
    public function message()
    {
        // MIMEタイプからファイル拡張子部分(例:"image/jpeg"から"jpeg")を抽出
        $extension = explode('/', $this->allowedMimeType)[1];

        return $extension. '形式の画像を選択してください。';
    }
}
