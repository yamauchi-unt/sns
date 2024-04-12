<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ユーザ新規登録 機能テスト
 */
class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * URL
     */
    protected $path = '/api/users';

    /**
     * 有効なユーザデータ
     */
    protected $userData = [
        'user_id'   => 'Test_User',
        'user_name' => 'Test User',
        'password'  => 'password',
    ];

    /**
     * 有効なリクエスト（正常）
     */
    public function test_register_with_valid_request_return_201(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->json('POST', $this->path, $userData);
        $user = User::where('user_id', $userData['user_id'])->first();
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($userData['user_id'], $user->user_id);
        $this->assertEquals($userData['user_name'], $user->user_name);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    /**
     * ContentType無でリクエスト（異常）
     */
    public function test_register_without_content_type_return_400(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->post($this->path, $userData, []);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * リクエストボディを空でリクエスト（異常）
     */
    public function test_register_without_request_body_return_400(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->post($this->path, [], ['Content-Type' => 'application/json']);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * ContentTypeをJSON、ボディをXML形式でリクエスト（異常）
     */
    public function test_register_with_content_type_json_and_request_body_xml_return_400(): void
    {
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?><user><user_id>Test_User</user_id><user_name>Test User</user_name><password>password</password></user>';
        // Act
        $response = $this->call('POST', $this->path, [], [], [], ['CONTENT_TYPE' => 'application/json'], $xmlData);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

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
     * 無効なContent-Type(application/json以外)でリクエスト（異常）
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_register_with_invalid_content_types_return_400($contentType)
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->withHeaders(['Content-Type' => $contentType])
                         ->post($this->path, $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * 文字列型のフィールドをint型でリクエスト（異常）
     */
    public function test_registr_with_int_type_in_string_fields_return_400(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 99,
            'user_name' => 99,
            'password'  => 99,
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * 文字列型のフィールドをarray型でリクエスト（異常）
     */
    public function test_registr_with_array_type_in_string_fields_return_400(): void
    {
        // Arrange
        $userData = [
            'user_id'   => ['Test_User'],
            'user_name' => ['Test User'],
            'password'  => ['password'],
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_registr_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => '',
            'user_name' => '',
            'password'  => '',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => parent::ERROR_MSG['user_id']['required'],
                    'user_name' => parent::ERROR_MSG['user_name']['required'],
                    'password'  => parent::ERROR_MSG['password']['required'],
        ]);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_registr_without_required_field_keys_return_422(): void
    {
        // Arrange
        $userData = [
            '' => 'Test_User',
            '' => 'Test User',
            '' => 'password',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => parent::ERROR_MSG['user_id']['required'],
                    'user_name' => parent::ERROR_MSG['user_name']['required'],
                    'password'  => parent::ERROR_MSG['password']['required'],
        ]);
        $this->assertCount(0, User::all());
    }

    /**
     * 必須項目をmullでリクエスト（異常）
     */
    public function test_registr_with_required_fields_null_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => null,
            'user_name' => null,
            'password'  => null,
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => parent::ERROR_MSG['user_id']['required'],
                    'user_name' => parent::ERROR_MSG['user_name']['required'],
                    'password'  => parent::ERROR_MSG['password']['required'],
        ]);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * "user_id"を最大長、"user_name"を最大長でリクエスト（正常）
     */
    public function test_registr_with_userid_maxlength_and_username_maxlength_return_201(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User123456789012345678901',
            'user_name' => 'Test User123456789012345678901',
            'password'  => 'Password1',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        $user = User::where('user_id', $userData['user_id'])->first();
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($userData['user_id'], $user->user_id);
        $this->assertEquals($userData['user_name'], $user->user_name);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    /**
     * "user_id"を最大長超過、"user_name"を最大長超過でリクエスト（異常）
     */
    public function test_registr_with_userid_overlength_and_username_overlength_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User1234567890123456789012',
            'user_name' => 'Test User1234567890123456789012',
            'password'  => 'Password1',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => parent::ERROR_MSG['user_id']['max'],
                    'user_name' => parent::ERROR_MSG['user_name']['max'],
        ]);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * "user_id"を登録済ユーザIDでリクエスト（異常）
     */
    public function test_register_with_registered_userid_return_422(): void
    {
        // Arrange
        $registeredUser = User::factory()->create();
        $userData = [
            'user_id'   => $registeredUser->user_id,
            'user_name' => 'Test User',
            'password'  => 'password',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id' => parent::ERROR_MSG['user_id']['unique'],
        ]);
        $this->assertCount(1, User::where('user_id', $registeredUser->user_id)->get());
    }

    /**
     * "user_id"を不正FMT、"password"を不正FMTでリクエスト1（異常）
     */
    public function test1_registr_with_userid_invalidfmt_and_password_invalidfmt_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User-!?<>',
            'user_name' => 'Test User',
            'password'  => 'Pass123',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['regex'],
                    'password' => parent::ERROR_MSG['password']['regex'],
        ]);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }

    /**
     * "user_id"を不正FMT、"password"を不正FMTでリクエスト2（異常）
     */
    public function test2_registr_with_userid_invalidfmt_and_password_invalidfmt_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_UserＡあア',
            'user_name' => 'Test User',
            'password'  => 'Pass_-!@',
        ];
        // Act
        $response = $this->json('POST', $this->path, $userData);
        // Assert
        $response->assertStatus(422)
                    ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['regex'],
                    'password' => parent::ERROR_MSG['password']['regex'],
        ]);
        $this->assertDatabaseMissing('users', ['user_id' => $userData['user_id']]);
    }
}
