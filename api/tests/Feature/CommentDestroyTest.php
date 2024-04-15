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
 * コメント削除 機能テスト
 */
class CommentDestroyTest extends TestCase
{
    use RefreshDatabase;

    protected $user1;
    protected $user2;
    protected $post;
    protected $comment1;
    protected $comment2;
    protected $token;
    protected $headers;
    protected $postData;

    /**
     * URL
     */
    protected $path = '/api/comments/';

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
        // テスト用コメント
        $this->comment1 = Comment::factory()->create(['id' => 1, 'post_id' => 1, 'user_id' => $this->user1->user_id]);
        $this->comment2 = Comment::factory()->create(['id' => 2, 'post_id' => 1, 'user_id' => $this->user2->user_id]);
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
    public function test_destroy_with_valid_request_return_204(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path. '1');
        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('comments', ['id' => $this->comment1->id]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_destroy_without_authorization_return_401(): void
    {
        // Act
        $response = $this->withHeaders(['Accept' => 'application/json',])->delete($this->path. '1');
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('comments', ['id' => $this->comment1->id]);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_destroy_with_invalid_token_return_401(): void
    {
        // Arrange
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->withHeaders([
                            'Authorization' => 'Bearer ' . $invalidToken,
                            'Accept' => 'application/json',
                        ])->delete($this->path. '1');
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('comments', ['id' => $this->comment1->id]);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_destroy_with_expired_token_return_401(): void
    {
        // Arrange
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user1->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path. '1');
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseHas('comments', ['id' => $this->comment1->id]);
    }

    /**
     * ルートパラメータへ他人の投稿IDを渡しリクエスト（異常）
     */
    public function test_destroy_others_return_403(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path. '2');
        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseHas('comments', ['id' => $this->comment2->id]);
    }

    /**
     * ルートパラメータへ存在しない投稿IDを渡しリクエスト（異常）
     */
    public function test_destroy_with_not_exist_postid_return_404(): void
    {
        // Act
        $response = $this->withHeaders($this->headers)->delete($this->path. '99');
        // Assert
        $response->assertStatus(404);
    }
}
