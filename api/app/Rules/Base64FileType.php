<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64FileType implements Rule
{
    protected $allowedMimeTypes;

    /**
     * @param array $allowedMimeTypes
     */
    public function __construct(array $allowedMimeTypes)
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
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

        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * エラーメッセージ
     *
     * @return string
     */
    public function message()
    {
        // MIMEタイプからファイル拡張子部分(例:"image/jpeg"から"jpeg")を抽出する関数
        $extractExtension = function($mimeType) {
            $parts = explode('/', $mimeType);
            return $parts[1];
        };

        // 抽出実行
        $allowedExtensions = array_map($extractExtension, $this->allowedMimeTypes);

        // 抽出したファイル形式を文字列として結合
        $allowedExtensionsStr = implode(', ', $allowedExtensions);

        return $allowedExtensionsStr. '形式の画像を選択してください。';
    }
}
