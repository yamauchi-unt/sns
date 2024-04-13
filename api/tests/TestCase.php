<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * 無効なContentType
     */
    public static function providerInvalidContentTypes()
    {
        return [
            ['multipart/form-data'],
            ['application/x-www-form-urlencoded'],
            ['text/plain'],
            ['application/javascript'],
            ['text/html'],
            ['application/xml'],
        ];
    }

    /**
     * バリデーションのエラーメッセージ
     */
    const ERROR_MSG = [
        'user_id' => [
            'required' => 'ユーザIDを入力してください。',
            'string'   => 'ユーザIDを正しく入力してください。',
            'max'      => 'ユーザIDは30文字以内で入力してください。',
            'unique'   => '既に登録されています。異なるユーザIDを入力してください。',
            'regex'    => 'ユーザIDは半角英数字_(ｱﾝﾀﾞｰｽｺｱ)のみで入力してください。',
        ],
        'user_name' => [
            'required' => 'ユーザ名を入力してください。',
            'string'   => 'ユーザ名を正しく入力してください。',
            'max'      => 'ユーザ名は30文字以内で入力してください。',
        ],
        'password' => [
            'required' => 'パスワードを入力してください。',
            'string'   => 'パスワードを正しく入力してください。',
            'regex'    => 'パスワードは半角英数字8文字以上で入力してください。',
        ],
    ];
}
