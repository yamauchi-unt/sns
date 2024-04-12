<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

// ユーザ新規登録 機能テスト
class UserRegisterTest extends TestCase
{
    use RefreshDatabase;

    // 有効なユーザデータ
    protected $userData = [
        'user_id'   => 'Test_User',
        'user_name' => 'Test User',
        'password'  => 'password',
    ];

    // 有効なデータで登録成功
    public function test_register_with_valid_data_return_201(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        $user = User::where('user_id', $userData['user_id'])->first();
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($userData['user_id'], $user->user_id);
        $this->assertEquals($userData['user_name'], $user->user_name);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    // ContentTypeヘッダー無で登録失敗
    public function test_register_without_content_type_return_400(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->post('/api/users', $userData, []);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // ContentTypeヘッダーJSON、リクエストボディ無で登録失敗
    public function test_register_with_json_contenttype_and_no_requestbody_return_400(): void
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->withHeaders(['Content-Type' => 'application/json'])
                         ->post('/api/users');
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // ContentTypeヘッダーJSON、リクエストボディXML形式で登録失敗
    public function test_register_with_json_contenttype_and_xml_requestbody_return_400(): void
    {
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?><user><user_id>Test_User</user_id><user_name>Test User</user_name><password>password</password></user>';
        // Act
        $response = $this->call('POST', '/api/users', [], [], [], ['CONTENT_TYPE' => 'application/json'], $xmlData);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // 無効なContentTypeのテストケース
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
     * 無効なContent-Type(application/json以外)で登録失敗
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_register_with_invalid_contenttypes_return_400($contentType)
    {
        // Arrange
        $userData = $this->userData;
        // Act
        $response = $this->withHeaders(['Content-Type' => $contentType])
                         ->post('/api/users', $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // 文字列型のフィールドにint型で登録失敗
    public function test_registr_with_int_type_in_string_fields_return_400(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 99,
            'user_name' => 99,
            'password'  => 99,
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // 文字列型のフィールドにarray型で登録失敗
    public function test_registr_with_array_type_in_string_fields_return_400(): void
    {
        // Arrange
        $userData = [
            'user_id'   => ['Test_User'],
            'user_name' => ['Test User'],
            'password'  => ['password'],
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(400);
        $this->assertCount(0, User::all());
    }

    // 必須項目が空で登録失敗
    public function test_registr_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => '',
            'user_name' => '',
            'password'  => '',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['required'],
                    'user_name' => $this->errorMessage['user_name']['required'],
                    'password'  => $this->errorMessage['password']['required'],
        ]);
        $this->assertCount(0, User::all());
    }

    // 必須項目がキー無で登録失敗
    public function test_registr_without_required_field_keys_return_422(): void
    {
        // Arrange
        $userData = [
            '' => 'Test_User',
            '' => 'Test User',
            '' => 'password',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['required'],
                    'user_name' => $this->errorMessage['user_name']['required'],
                    'password'  => $this->errorMessage['password']['required'],
        ]);
        $this->assertCount(0, User::all());
    }

    // 必須項目がnullで登録失敗
    public function test_registr_with_null_required_fields_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => null,
            'user_name' => null,
            'password'  => null,
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['required'],
                    'user_name' => $this->errorMessage['user_name']['required'],
                    'password'  => $this->errorMessage['password']['required'],
        ]);
        $this->assertCount(0, User::all());
    }

    // "user_id"が最大長、"user_name"が最大長で登録成功
    public function test_registr_with_userid_maxlength_and_username_maxlength_return_201(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User123456789012345678901',
            'user_name' => 'Test User123456789012345678901',
            'password'  => 'Password1',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        $user = User::where('user_id', $userData['user_id'])->first();
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($userData['user_id'], $user->user_id);
        $this->assertEquals($userData['user_name'], $user->user_name);
        $this->assertTrue(Hash::check($userData['password'], $user->password));
    }

    // "user_id"が最大長超過、"user_name"が最大長超過で登録失敗
    public function test_registr_with_userid_overlength_and_username_overlength_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User1234567890123456789012',
            'user_name' => 'Test User1234567890123456789012',
            'password'  => 'Password1',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['max'],
                    'user_name' => $this->errorMessage['user_name']['max'],
        ]);
        $this->assertCount(0, User::all());
    }

    // "user_id"が登録済で登録失敗
    public function test_register_with_userid_registered_return_422(): void
    {
        // Arrange
        $registeredUser = User::factory()->create();
        $userData = $this->userData;
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['unique'],
        ]);
        $this->assertCount(1, User::all());
    }

    // "user_id"が不正FMT、"password"が不正FMTで登録失敗
    public function test_registr_with_userid_invalidfmt_and_password_invalidfmt_1_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_User-!?<>',
            'user_name' => 'Test User',
            'password'  => 'Pass123',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['regex'],
                    'password' => $this->errorMessage['password']['regex'],
        ]);
        $this->assertCount(0, User::all());
    }

    // "user_id"が不正FMT、"password"が不正FMTで登録失敗
    public function test_registr_with_userid_invalidfmt_and_password_invalidfmt_2_return_422(): void
    {
        // Arrange
        $userData = [
            'user_id'   => 'Test_UserＡあア',
            'user_name' => 'Test User',
            'password'  => 'Pass_-!@',
        ];
        // Act
        $response = $this->json('POST', '/api/users', $userData);
        // Assert
        $response->assertStatus(422)
                    ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['regex'],
                    'password' => $this->errorMessage['password']['regex'],
        ]);
        $this->assertCount(0, User::all());
    }
}
