<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

// トークン取得 機能テスト
class AuthTokenStoreTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    // テスト実行前に登録済ユーザ作成
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // 有効なログインデータ
    protected $validLoginData = [
        'user_id'  => 'Test_User',
        'password' => 'password',
    ];

    // 有効なデータで取得成功
    public function test_store_with_valid_data_return_200(): void
    {
        // Arrange
        $loginData = $this->validLoginData;
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        $planTextToken = $response->json('token');
        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
        $this->assertNotNull($planTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // ContentTypeヘッダー無で取得失敗
    public function test_store_without_content_type_return_400(): void
    {
        // Arrange
        $loginData = $this->validLoginData;
        // Act
        $response = $this->post('/api/auth/token', $loginData, []);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // 文字列型のフィールドにint型で取得失敗
    public function test_store_with_int_type_in_string_fields_return_400(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 99,
            'password' => 99,
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // 文字列型のフィールドにarray型で登録失敗
    public function test_store_with_array_type_in_string_fields_return_400(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => ['Test_User'],
            'password' => ['password'],
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // 必須項目が空で登録失敗
    public function test_registr_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'   => '',
            'password'  => '',
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(422)
                    ->assertJsonValidationErrors([
                    'user_id'   => $this->errorMessage['user_id']['required'],
                    'password'  => $this->errorMessage['password']['required'],
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // 必須項目がキー無で登録失敗
    public function test_store_without_required_field_keys_return_422(): void
    {
        // Arrange
        $loginData = [
            '' => 'Test_User',
            '' => 'password',
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => $this->errorMessage['user_id']['required'],
                    'password' => $this->errorMessage['password']['required'],
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // 必須項目がnullで登録失敗
    public function test_store_with_null_required_fields_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => null,
            'password' => null,
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => $this->errorMessage['user_id']['required'],
                    'password' => $this->errorMessage['password']['required'],
        ]);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // "user_id"が不正で取得失敗
    public function test_store_with_userid_invalid_return_401(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 'Invalid_User',
            'password' => 'password',
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }

    // "password"が不正で取得失敗
    public function test_store_with_password_invalid_return_401(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 'Test_User',
            'password' => 'password1',
        ];
        // Act
        $response = $this->json('POST', '/api/auth/token', $loginData);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $this->user->id,
        ]);
    }
}
