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
     * 有効な画像形式のエンコード文字列のファイルパス
     */
    protected $validImagePath = 'tests/datas/jpeg_image_base64.txt';

    /**
     * 無効な画像形式のエンコード文字列のファイルパス
     */
    public static function providerInvalidImageTypesPath()
    {
        return [
            ['tests/datas/png_image_base64.txt'],
            ['tests/datas/gif_image_base64.txt'],
            ['tests/datas/svg_image_base64.txt'],
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
        'image' => [
            'required' => '画像を選択してください。',
            'size'     => '画像サイズは5MB以下にしてください。',
            'fmt'      => 'JPEG形式の画像を選択してください。',
        ],
        'message' => [
            'required' => '本文を入力してください。',
        ],
        'comment' => [
            'required' => 'コメントを入力してください。',
            'string'   => 'コメントを正しく入力してください。',
            'max'      => 'コメントは255文字以内で入力してください。',
        ],
        'current_password' => [
            'required' => '現在のパスワードを入力してください。',
            'string'   => '現在のパスワードを正しく入力してください。',
            'match'    => '現在のパスワードを正しく入力してください。',
        ],
        'new_password' => [
            'required' => '新しいパスワードを入力してください。',
            'string'   => '新しいパスワードを正しく入力してください。',
            'regex'    => 'パスワードは半角英数字8文字以上で入力してください。',
        ]
    ];
}
