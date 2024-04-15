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
 * コメント取得 機能テスト
 */
class CommentIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $user1;
    protected $user2;
    protected $post;
    protected $token;
    protected $headers;
    protected $postData;

    /**
     * URL
     */
    protected $path = '/api/posts/1/comments';

    /**
     * 各テストメソッド実行前に毎回実行
     */
    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザ1とユーザ2
        $this->user1 = User::factory()->create(['user_id' => 'Test_User1']);
        $this->user2 = User::factory()->create(['user_id' => 'Test_User2']);
        // テスト用投稿（投稿IDが1）
        $this->post = Post::factory()->create(['id' => 1, 'user_id' => $this->user1->user_id]);
        // テスト用コメント（コメントIDが1）
        Comment::factory()->create(['id' => 1, 'post_id' => 1, 'user_id' => $this->user2->user_id]);
        // テスト用コメント（コメントIDが2~26）
        Comment::factory()->count(25)
                        ->sequence( fn ($sequence) => [
                            'id' => $sequence->index + 2,
                            'post_id' => 1,
                            'user_id' => $this->user1->user_id])
                        ->create();
        // テスト用トークン
        $this->token = $this->user1->createToken('test-token')->plainTextToken;
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
                    'total' => 26,
                    'per_page' => 10,
                    'next_page_url' => 'http://localhost/api/posts/1/comments?page=2'])
                 ->assertJsonStructure(['data'=> [
                    '*' => ['comment_id', 'post_id', 'mine_frg', 'user_name', 'comment']
                    ]]);

        $this->assertEquals(26, $responseData['data'][0]['comment_id']);
        $this->assertEquals(17, $responseData['data'][9]['comment_id']);
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
                    'total' => 26,
                    'per_page' => 10,
                    'next_page_url' => 'http://localhost/api/posts/1/comments?page=3'])
                 ->assertJsonStructure(['data'=> [
                    '*' => ['comment_id', 'post_id', 'mine_frg', 'user_name', 'comment']
                    ]]);

        $this->assertEquals(16, $responseData['data'][0]['comment_id']);
        $this->assertEquals(7, $responseData['data'][9]['comment_id']);
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
                    'total' => 26,
                    'per_page' => 10,
                    'next_page_url' => null])
                 ->assertJsonStructure(['data'=> [
                    '*' => ['comment_id', 'post_id', 'mine_frg', 'user_name', 'comment']
                    ]]);

        $this->assertEquals(6, $responseData['data'][0]['comment_id']);
        $this->assertEquals(2, $responseData['data'][4]['comment_id']);
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
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user1->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path);
        // Assert
        $response->assertStatus(401);
    }

    /**
     * ルートパラメータに存在しない投稿IDを渡しリクエスト（異常）
     */
    public function test_index_with_not_exist_postid_return_404(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get('/api/posts/99/comments');
        // Assert
        $response->assertStatus(404);
    }

    /**
     * ルートパラメータに存在しないページ数を渡しリクエスト（異常）
     */
    public function test_index_mypost_with_not_exist_page_return_404(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->get($this->path. '?page=99');
        // Assert
        $response->assertStatus(404);
    }
}
