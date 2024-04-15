<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * プロフィール編集 機能テスト
 */
class MyprofileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $headers;
    protected $currentData;
    protected $updateData;

    /**
     * URL
     */
    protected $path = '/api/myprofile';

    /**
     * 各テストメソッド実行前に毎回実行
     */
    protected function setUp(): void
    {
        parent::setUp();
        // 現在のユーザデータ
        $this->currentData  = [
            'user_id'   => 'Test_User',
            'user_name' => 'Test User',
            'password'  => 'password',
        ];
        // テスト用ユーザ
        $this->user = User::factory()->create($this->currentData);
        // テスト用トークン
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
        // 更新データ
        $this->updateData = [
            'user_name'        => 'Update User',
            'current_password' => '',
            'new_password'     => '',
        ];
    }

    /**
     * "user_name"のみ編集する有効なリクエスト（正常）
     */
    public function test_update_with_valid_user_name_return_200(): void
    {
        // Arrange
        $updateData = $this->updateData;
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(200);
        $this->assertEquals($this->currentData['user_id'], $this->user->user_id);
        $this->assertEquals($this->updateData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * "user_name"と"password"を編集する有効なリクエスト（正常）
     */
    public function test2_update_with_valid_request_return_200(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Update User',
            'current_password' => 'password',
            'new_password'     => 'updatePassword',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(200);
        $this->assertEquals($this->currentData['user_id'], $this->user->user_id);
        $this->assertEquals($this->updateData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($updateData['new_password'], $this->user->password));
    }

    /**
     * ContentType無でリクエスト（異常）
     */
    public function test_update_without_content_type_return_400(): void
    {
        // Arrange
        $updateData = $this->updateData;
        // Act
        $response = $this->patch($this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(400);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_update_without_authorization_return_401(): void
    {
        // Arrange
        $updateData = $this->updateData;
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, ['Accept' => 'application/json']);
        $this->user->refresh();
        // Assert
        $response->assertStatus(401);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_update_with_invalid_token_return_401(): void
    {
        // Arrange
        $updateData = $this->updateData;
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, [
                                    'Authorization' => 'Bearer ' . $invalidToken,
                                    'Accept' => 'application/json'
                                ]);
        $this->user->refresh();
        // Assert
        $response->assertStatus(401);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_update_with_expired_token_return_401(): void
    {
        // Arrange
        $updateData = $this->updateData;
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(401);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_update_with_empty_username_and_empty_currentpassword_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => '',
            'current_password' => '',
            'new_password'     => 'updatePassword',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['required'],
                    'current_password' => parent::ERROR_MSG['current_password']['required'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_update_with_empty_newpassword_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Update User',
            'current_password' => 'password',
            'new_password'     => '',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'new_password' => parent::ERROR_MSG['new_password']['required'],
                ]);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_update_without_username_key_and_currntpassword_key_return_422(): void
    {
        // Arrange
        $updateData = [
            ''             => 'Update User',
            ''             => 'password',
            'new_password' => 'updatePassword',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['required'],
                    'current_password' => parent::ERROR_MSG['current_password']['required'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_update_without_newpassword_key_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Update User',
            'current_password' => 'password',
            ''                 => 'updatePassword',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'new_password' => parent::ERROR_MSG['new_password']['required'],
                ]);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 必須項目をnullでリクエスト（異常）
     */
    public function test_update_with_username_null_and_currentpassword_null_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => null,
            'current_password' => null,
            'new_password'     => 'updatePassword',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['required'],
                    'current_password' => parent::ERROR_MSG['current_password']['required'],
                ]);
    $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
    $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 必須項目をnullでリクエスト（異常）
     */
    public function test_update_with_newpassword_null_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Update User',
            'current_password' => 'password',
            'new_password'     => null,
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'new_password' => parent::ERROR_MSG['new_password']['required'],
                ]);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 文字列型のフィールドをint型でリクエスト（異常）
     */
    public function test_update_with_int_type_in_string_fields_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 99,
            'current_password' => 99999999,
            'new_password'     => 99999999,
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['string'],
                    'current_password' => parent::ERROR_MSG['current_password']['string'],
                    'new_password' => parent::ERROR_MSG['new_password']['string'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * 文字列型のフィールドをarray型でリクエスト（異常）
     */
    public function test_update_with_array_type_in_string_fields_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => ['Update User'],
            'current_password' => ['password'],
            'new_password'     => ['updatePassword'],
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['string'],
                    'current_password' => parent::ERROR_MSG['current_password']['string'],
                    'new_password' => parent::ERROR_MSG['new_password']['string'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * "user_name"を最大長でリクエスト（正常）
     */
    public function test_store_with_comment_maxlength_return_201(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Lorem ipsum dolor sit amet, co',
            'current_password' => '',
            'new_password'     => '',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(200);
        $this->assertEquals($updateData['user_name'], $this->user->user_name);
    }

    /**
     * "user_name"を最大長超過、不正な"current_password"、"new_password"を不正なFMTでリクエスト（異常）
     */
    public function test_store_with_comment_overlength_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Lorem ipsum dolor sit amet, com',
            'current_password' => 'invalidPassword',
            'new_password'     => 'invalid',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'user_name'   => parent::ERROR_MSG['user_name']['max'],
                    'current_password' => parent::ERROR_MSG['current_password']['match'],
                    'new_password' => parent::ERROR_MSG['password']['regex'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }

    /**
     * "new_password"を不正なFMTでリクエスト（異常）
     */
    public function test_store_with_invalid_newpassword_return_422(): void
    {
        // Arrange
        $updateData = [
            'user_name'        => 'Update User',
            'current_password' => 'password',
            'new_password'     => 'pass_-!@',
        ];
        // Act
        $response = $this->json('PATCH', $this->path, $updateData, $this->headers);
        $this->user->refresh();
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'new_password' => parent::ERROR_MSG['password']['regex'],
                ]);
        $this->assertEquals($this->currentData['user_name'], $this->user->user_name);
        $this->assertTrue(Hash::check($this->currentData['password'], $this->user->password));
    }
}
