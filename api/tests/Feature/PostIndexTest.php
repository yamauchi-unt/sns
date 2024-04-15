<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Type\VoidType;
use Tests\TestCase;

/**
 * 投稿取得 機能テスト
 */
class PostIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;
    protected $token;
    protected $headers;
    protected $postData;

    /**
     * URL
     */
    protected $path = '/api/posts';

    /**
     * 各テストメソッド実行前に毎回実行
     */
    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザ
        $this->user = User::factory()->create();
        // テスト用投稿（投稿IDが1~25）
        $this->post = Post::factory()->count(25)
                                     ->sequence( fn ($sequence) => [
                                            'id' => $sequence->index + 1,
                                            'user_id' => $this->user->user_id
                                        ])
                                     ->create();
        // テスト用トークン
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * 開始ページの有効なリクエスト（正常）
     */
    public function test_index_first_page_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path);
        $responseData = $response->json();
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'total' => 25,
                    'per_page' => 10,
                    'next_page_url' => 'http://localhost/api/posts?page=2'])
                 ->assertJsonStructure(['data'=> [ '*' => ['id']]]);

        $this->assertEquals(25, $responseData['data'][0]['id']);
        $this->assertEquals(16, $responseData['data'][9]['id']);
    }

    /**
     * 2ページ目の有効なリクエスト（正常）
     */
    public function test_index_second_page_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '?page=2');
        $responseData = $response->json();
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'total' => 25,
                    'per_page' => 10,
                    'next_page_url' => 'http://localhost/api/posts?page=3'])
                 ->assertJsonStructure(['data'=> [ '*' => ['id']]]);

        $this->assertEquals(15, $responseData['data'][0]['id']);
        $this->assertEquals(6, $responseData['data'][9]['id']);
    }

    /**
     * 最終ページの有効なリクエスト（正常）
     */
    public function test_index_last_page_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '?page=3');
        $responseData = $response->json();
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'total' => 25,
                    'per_page' => 10,
                    'next_page_url' => null])
                 ->assertJsonStructure(['data'=> [ '*' => ['id']]]);

        $this->assertEquals(5, $responseData['data'][0]['id']);
        $this->assertEquals(1, $responseData['data'][4]['id']);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_index_without_authorization_return_401(): void
    {
        // Act
        $response = $this->withHeaders(['Accept' => 'application/json',])->get($this->path);
        // Assert
        $response->assertStatus(401);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_index_with_invalid_token_return_401(): void
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
    public function test_index_with_expired_token_return_401(): void
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

    /**
     * ルートパラメータを存在しないページ数にしリクエスト（異常）
     */
    public function test_index_with_not_exist_page_return_404(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '?page=99');
        // Assert
        $response->assertStatus(404);
    }
}
