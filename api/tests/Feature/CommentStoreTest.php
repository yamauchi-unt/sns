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
 * コメント送信 機能テスト
 */
class CommentStoreTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $post;
    protected $token;
    protected $headers;
    protected $commentData;

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
        // テスト用ユーザ
        $this->user = User::factory()->create(['user_id' => 'Test_User']);
        // テスト用投稿
        $this->post = Post::factory()->create(['id' => 1, 'user_id' => $this->user->user_id]);
        // テスト用トークン
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
        // テスト用有効な投稿データ
        $this->commentData = ['comment' => 'test_comment'];
    }

    /**
     * 有効なリクエスト（正常）
     */
    public function test_store_with_valid_request_return_201(): void
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        $comment = Comment::where('user_id', $this->user->user_id)->first();
        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', ['user_id' => $this->user->user_id]);
        $this->assertEquals($commentData['comment'], $comment->comment);
    }

    /**
     * ContentType無でリクエスト（異常）
     */
    public function test_store_without_content_type_return_400(): void
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->post($this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

        /**
     * リクエストボディを空でリクエスト（異常）
     */
    public function test_store_without_request_body_return_400(): void
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->post($this->path, [], [
                                    'Content-Type'  => 'application/json',
                                    'Authorization' => 'Bearer ' . $this->token,
                                    'Accept'        => 'application/json'
                                ]);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * ContentTypeをJSON、ボディをXML形式でリクエスト（異常）
     */
    public function test_store_with_content_type_json_and_request_body_xml_return_400(): void
    {
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8" ?><root><comment>test_comment</comment></root>';
        // Act
        $response = $this->call('POST', $this->path, [], [], [], [
                                    'Content-Type'  => 'application/json',
                                    'Authorization' => 'Bearer ' . $this->token,
                                    'Accept'        => 'application/json',
                                ], $xmlData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 無効なContent-Typeでリクエスト（異常）
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_store_with_invalid_content_types_return_400($contentType)
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->withHeaders(['Content-Type'  => $contentType,
                                        'Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->post($this->path, $commentData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_store_without_authorization_return_401(): void
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->json('POST', $this->path, $commentData, ['Accept' => 'application/json']);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_store_with_invalid_token_return_401(): void
    {
        // Arrange
        $commentData = $this->commentData;
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->json('POST', $this->path, $commentData, [
                                    'Authorization' => 'Bearer ' . $invalidToken,
                                    'Accept' => 'application/json'
                                ]);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_store_with_expired_token_return_401(): void
    {
        // Arrange
        $commentData = $this->commentData;
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_store_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $commentData = ['comment' => ''];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment' => parent::ERROR_MSG['comment']['required']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_store_without_required_field_keys_return_422(): void
    {
        // Arrange
        $commentData = ['' => 'test_comment'];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment' => parent::ERROR_MSG['comment']['required']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目をnullでリクエスト（異常）
     */
    public function test_store_with_required_fields_null_return_422(): void
    {
        // Arrange
        $commentData = ['comment' => null];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment' => parent::ERROR_MSG['comment']['required']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 文字列型のフィールドをint型でリクエスト（異常）
     */
    public function test_store_with_int_type_in_string_fields_return_422(): void
    {
        // Arrange
        $commentData = ['comment' => 99];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment' => parent::ERROR_MSG['comment']['string']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * 文字列型のフィールドをarray型でリクエスト（異常）
     */
    public function test_store_with_array_type_in_string_fields_return_422(): void
    {
        // Arrange
        $commentData = ['comment' => ['test_comment']];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment' => parent::ERROR_MSG['comment']['string']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * "comment"を最大長でリクエスト（正常）
     */
    public function test_store_with_comment_maxlength_return_201(): void
    {
        // Arrange
        $commentData = ['comment' => 'つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて'];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        $comment = Comment::where('user_id', $this->user->user_id)->first();
        // Assert
        $response->assertStatus(201);
        $this->assertEquals($this->user->user_id, $comment->user_id);
        $this->assertEquals($commentData['comment'], $comment->comment);
    }

    /**
     * "comment"を最大長超過でリクエスト（異常）
     */
    public function test_store_with_comment_overlength_return_422(): void
    {
        // Arrange
        $commentData = ['comment' => 'つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて、心にうつりゆくよしなし事を、そこはかとなく書きつくれば、あやしうこそものぐるほしけれ。（Wikipediaより）つれづれなるまゝに、日暮らし、硯にむかひて、'];
        // Act
        $response = $this->json('POST', $this->path, $commentData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['comment'   => parent::ERROR_MSG['comment']['max']]);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }

    /**
     * ルートパラメータへ存在しない投稿IDを渡しリクエスト（異常）
     */
    public function test_store_with_not_exist_postid_return_404(): void
    {
        // Arrange
        $commentData = $this->commentData;
        // Act
        $response = $this->json('POST', '/api/posts/99/comments', $commentData, $this->headers);
        // Assert
        $response->assertStatus(404);
        $this->assertDatabaseMissing('comments', ['user_id' => $this->user->user_id]);
    }
}
