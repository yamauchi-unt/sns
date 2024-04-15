<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * トークン削除 機能テスト
 */
class AuthTokenDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $tokenId;
    protected $headers;

    /**
     * URL
     */
    protected $path = '/api/auth/token';

    /**
     * 各テストメソッド実行前に、テスト用ユーザ・トークン・リクエストヘッダー作成
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->tokenId = explode('|', $this->token, 2)[0];
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * 有効なリクエスト（正常）
     */
    public function test_destroy_with_valid_request_return_204(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path);
        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $this->tokenId]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_destroy_without_authorization_return_401(): void
    {
        // Act
        $response = $this->withHeaders(['Accept' => 'application/json'])
                         ->delete($this->path);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $this->tokenId]);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_destroy_with_invalid_token_return_401(): void
    {
        // Arrange
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $invalidToken,
                                        'Accept'        => 'application/json'])
                         ->delete($this->path);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $this->tokenId]);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_destroy_with_expired_token_return_401(): void
    {
        // Arrange
        DB::table('personal_access_tokens')->where('id', $this->tokenId)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $this->tokenId]);
    }
}
