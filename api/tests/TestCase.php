<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    // バリデーションのエラーメッセージ
    protected $errorMessage = [
        'user_id' => [
            'required' => 'ユーザIDを入力してください。',
            'max'      => 'ユーザIDは30文字以内で入力してください。',
            'unique'   => '既に登録されています。異なるユーザIDを入力してください。',
            'regex'    => 'ユーザIDは半角英数字_(ｱﾝﾀﾞｰｽｺｱ)のみで入力してください。',
        ],
        'user_name' => [
            'required' => 'ユーザ名を入力してください。',
            'max' => 'ユーザ名は30文字以内で入力してください。',
        ],
        'password' => [
            'required' => 'パスワードを入力してください。',
            'regex'    => 'パスワードは半角英数字8文字以上で入力してください。',
        ],
    ];
}
