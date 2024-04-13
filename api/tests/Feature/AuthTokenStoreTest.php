<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * トークン取得 機能テスト
 */
class AuthTokenStoreTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    /**
     * URL
     */
    protected $path = '/api/auth/token';

    /**
     * 各テストメソッド実行前に、テスト用ユーザ作成
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /**
     * 有効なログインデータ（UserFactoryと同値）
     */
    protected $loginData = [
        'user_id'  => 'Test_User',
        'password' => 'password',
    ];

    /**
     * 有効なリクエスト（正常）
     */
    public function test_store_with_valid_request_return_200(): void
    {
        // Arrange
        $loginData = $this->loginData;
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        $planTextToken = $response->json('token');
        // Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
        $this->assertNotNull($planTextToken);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * ContentType無でリクエスト（異常）
     */
    public function test_store_without_content_type_return_400(): void
    {
        // Arrange
        $loginData = $this->loginData;
        // Act
        $response = $this->post($this->path, $loginData, []);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * リクエストボディを空でリクエスト（異常）
     */
    public function test_store_without_request_body_return_400(): void
    {
        // Arrange
        $loginData = $this->loginData;
        // Act
        $response = $this->post($this->path, [], ['Content-Type' => 'application/json']);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * ContentTypeをJSON、ボディをXML形式でリクエスト（異常）
     */
    public function test_store_with_content_type_json_and_request_body_xml_return_400(): void
    {
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?><user><user_id>Test_User</user_id><password>password</password></user>';
        // Act
        $response = $this->call('POST', $this->path, [], [], [], ['CONTENT_TYPE' => 'application/json'], $xmlData);
        $tokenRecordCount = \DB::table('personal_access_tokens')->count();
        // Assert
        $response->assertStatus(400);
        $this->assertEquals(0, $tokenRecordCount);
    }

    /**
     * 無効なContent-Typeでリクエスト（異常）
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_store_with_invalid_content_types_return_400($contentType)
    {
        // Arrange
        $loginData = $this->loginData;
        // Act
        $response = $this->withHeaders(['Content-Type' => $contentType])
                         ->post($this->path, $loginData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * "user_id"を不正な値でリクエスト（異常）
     */
    public function test_store_with_invalid_userid_return_401(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 'Invalid_User',
            'password' => 'password',
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * "password"を不正な値でリクエスト（異常）
     */
    public function test_store_with_invalid_password_return_401(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 'Test_User',
            'password' => 'password1',
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_store_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'   => '',
            'password'  => '',
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'   => parent::ERROR_MSG['user_id']['required'],
                    'password'  => parent::ERROR_MSG['password']['required'],
                ]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_store_without_required_field_keys_return_422(): void
    {
        // Arrange
        $loginData = [
            '' => 'Test_User',
            '' => 'password',
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['required'],
                    'password' => parent::ERROR_MSG['password']['required'],
                ]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * 必須項目をnullでリクエスト（異常）
     */
    public function test_store_with_required_fields_null_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => null,
            'password' => null,
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['required'],
                    'password' => parent::ERROR_MSG['password']['required'],
                ]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * 文字列型のフィールドをint型でリクエスト（異常）
     */
    public function test_store_with_int_type_in_string_fields_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => 99,
            'password' => 99999999,
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['string'],
                    'password' => parent::ERROR_MSG['password']['string'],
                ]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }

    /**
     * 文字列型のフィールドをarray型でリクエスト（異常）
     */
    public function test_store_with_array_type_in_string_fields_return_422(): void
    {
        // Arrange
        $loginData = [
            'user_id'  => ['Test_User'],
            'password' => ['password'],
        ];
        // Act
        $response = $this->json('POST', $this->path, $loginData);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_id'  => parent::ERROR_MSG['user_id']['string'],
                    'password' => parent::ERROR_MSG['password']['string'],
                ]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $this->user->id]);
    }
}
