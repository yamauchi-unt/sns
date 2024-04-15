<?php

namespace Tests\Feature;

use App\Models\Comment;
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
 * 投稿1件取得 機能テスト
 */
class PostShowTest extends TestCase
{
    use RefreshDatabase;

    protected $user1;
    protected $user2;
    protected $post1;
    protected $post2;
    protected $token;
    protected $headers;
    protected $postData;

    /**
     * URL
     */
    protected $path = '/api/posts/';

    /**
     * 各テストメソッド実行前に毎回実行
     */
    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザ1とユーザ2
        $this->user1 = User::factory()->create(['user_id' => 'Test_User1']);
        $this->user2 = User::factory()->create(['user_id' => 'Test_User2']);
        // テスト用投稿/コメント
        $this->post1 = Post::factory()->has(Comment::factory()->count(3))
                                     ->create(['id' => 1, 'user_id' => $this->user1->user_id]);
        $this->post2 = Post::factory()->create(['id' => 2, 'user_id' => $this->user2->user_id]);
        // テスト用トークン
        $this->token = $this->user1->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
    }

    /**
     * 有効なリクエスト（正常）
     */
    public function test_show_if_mypost_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '1');
        $responseData = $response->json();
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'post_id' => 1,
                    'mine_frg' => true,
                    'user_name' => $this->user1->user_name,
                    'message' => $this->post1->message,
                    'post_date' => $this->post1->created_at,
                    'comment_count' => '3'
                ]);
    }

        /**
     * 有効なリクエスト（正常）
     */
    public function test_show_if_others_with_valid_request_return_200(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '2');
        $responseData = $response->json();
        // Assert
        $response->assertStatus(200)
                 ->assertJson([
                    'post_id' => 2,
                    'mine_frg' => false,
                    'user_name' => $this->user2->user_name,
                    'message' => $this->post2->message,
                    'post_date' => $this->post2->created_at,
                    'comment_count' => '0'
                ]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_show_without_authorization_return_401(): void
    {
        // Act
        $response = $this->withHeaders(['Accept' => 'application/json',])->get($this->path. '1');
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
                        ])->get($this->path. '1');
        // Assert
        $response->assertStatus(401);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_index_mypost_with_expired_token_return_401(): void
    {
        // Arrange
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user1->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '1');
        // Assert
        $response->assertStatus(401);
    }

    /**
     * ルートパラメータを存在しない投稿IDでリクエスト（異常）
     */
    public function test_show_with_not_exist_postid_return_404(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '99');
        // Assert
        $response->assertStatus(404);
    }
}
