<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Base64MaxSize implements Rule
{
    protected $maxSize;

    /**
     * @param  int  $maxSize 最大サイズ（MB単位）
     */
    public function __construct(int $maxSize)
    {
        $this->maxSize = $maxSize;
    }

    /**
     * 画像サイズが上限以下かチェックする
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return strlen($value) <= $this->maxSize;
    }

    /**
     * エラーメッセージ
     *
     * @return string
     */
    public function message()
    {
        return ':attributeサイズは'. $this->maxSize / (1024 * 1024) . 'MB以下にしてください。';
    }
}
