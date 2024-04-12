<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// トークン削除 機能テスト
class AuthTokenDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $tokenId;

    // テスト実行前に登録済ユーザ・トークン生成
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->tokenId = explode('|', $this->token, 2)[0];
    }

    // 有効なトークンで削除成功
    public function test_destroy_with_token_valid_return_204(): void
    {
        // Act
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $this->token])
                         ->delete('/api/auth/token');
        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $this->tokenId,
        ]);
    }

    // Authorizationヘッダー無で削除失敗
    public function test_destroy_without_authorization_header_return_400(): void
    {
        // Act
        $response = $this->post('/api/auth/token');
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $this->tokenId,
        ]);
    }

    // 有効期限切れトークンで削除失敗
    public function test_destroy_with_token_invalid_return_401(): void
    {
        // Arrange
        $user = User::factory()->create([
            'user_id' => 'Invalid_User'
        ]);
        $token = $user->createToken('test-token')->plainTextToken;
        $tokenId = explode('|', $token, 2)[0];
        DB::table('personal_access_tokens')->where('id', $tokenId)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders(['Authorization' => 'Bearer '. $token,
                                        'Accept'        => 'application/json'])
                         ->delete('/api/auth/token');
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenId,
        ]);
    }
}
