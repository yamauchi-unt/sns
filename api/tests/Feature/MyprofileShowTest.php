<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * プロフィール取得 機能テスト
 */
class MyprofileShowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $headers;

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
        // テスト用ユーザ
        $this->user = User::factory()->create([
            'user_id'   => 'Test_User',
            'user_name' => 'Test User',
            'password'  => 'password',
        ]);
        // テスト用トークン
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * 有効なリクエスト（正常）
     */
    public function test_show_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path);
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'user_id'   => $this->user->user_id,
                    'user_name' => $this->user->user_name,
                ]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_show_without_authorization_return_401(): void
    {
        // Act
        $response = $this->withHeaders(['Accept' => 'application/json',])->get($this->path);
        // Assert
        $response->assertStatus(401);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_index_mypost_with_invalid_token_return_401(): void
    {
        // Arrange
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->withHeaders([
                            'Authorization' => 'Bearer ' . $invalidToken,
                            'Accept' => 'application/json',
                        ])->get($this->path);
        // Assert
        $response->assertStatus(401);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_index_mypost_with_expired_token_return_401(): void
    {
        // Arrange
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path);
        // Assert
        $response->assertStatus(401);
    }
}
