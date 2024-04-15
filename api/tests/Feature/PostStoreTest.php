<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 投稿 機能テスト
 */
class PostStoreTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;
    protected $headers;
    protected $postData;

    /**
     * URL
     */
    protected $path = '/api/posts';

    /**
     * 各テストメソッド実行前に実行
     */
    protected function setUp(): void
    {
        parent::setUp();
        // テスト用ユーザ
        $this->user = User::factory()->create();
        // テスト用トークン
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        // テスト用リクエストヘッダー
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept'        => 'application/json',
        ];
        // テスト用ストレージ
        Storage::fake('local');
        // テスト用有効な投稿データ
        $this->postData = [
            'image'   => file_get_contents(base_path($this->validImagePath)),
            'message' => 'test_image'
        ];
    }

    /**
     * 有効なリクエスト（正常）
     */
    public function test_store_with_valid_request_return_201(): void
    {
        // Arrange
        $postData = $this->postData;
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        $post = Post::where('user_id', $this->user->user_id)->first();
        // Assert
        $response->assertStatus(201)
                 ->assertExactJson(['post_id' => $post->id]);
        $this->assertEquals($postData['message'], $post->message);
        Storage::disk('local')->assertExists("images/{$post->id}.jpeg");
    }

    /**
     * ContentType無でリクエスト（異常）
     */
    public function test_store_without_content_type_return_400(): void
    {
        // Arrange
        $postData = $this->postData;
        // Act
        $response = $this->post($this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * リクエストボディを空でリクエスト（異常）
     */
    public function test_store_without_request_body_return_400(): void
    {
        // Arrange
        $postData = $this->postData;
        // Act
        $response = $this->post($this->path, [], [
                                    'Content-Type'  => 'application/json',
                                    'Authorization' => 'Bearer ' . $this->token,
                                    'Accept'        => 'application/json'
                                ]);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * ContentTypeをJSON、ボディをXML形式でリクエスト（異常）
     */
    public function test_store_with_content_type_json_and_request_body_xml_return_400(): void
    {
        $imageStr = file_get_contents(base_path($this->validImagePath));
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8" ?><root><image>'. $imageStr. '</image><message>test_message</message></root>';
        // Act
        $response = $this->call('POST', $this->path, [], [], [], [
                                    'Content-Type'  => 'application/json',
                                    'Authorization' => 'Bearer ' . $this->token,
                                    'Accept'        => 'application/json',
                                ], $xmlData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 無効なContent-Typeでリクエスト（異常）
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_store_with_invalid_content_types_return_400($contentType)
    {
        // Arrange
        $postData = $this->postData;
        // Act
        $response = $this->withHeaders(['Content-Type'  => $contentType,
                                        'Authorization' => 'Bearer ' . $this->token,
                                        'Accept'        => 'application/json'])
                         ->post($this->path, $postData);
        // Assert
        $response->assertStatus(400);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * Authorization無でリクエスト（異常）
     */
    public function test_store_without_authorization_return_401(): void
    {
        // Arrange
        $postData = $this->postData;
        // Act
        $response = $this->json('POST', $this->path, $postData, ['Accept' => 'application/json']);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 不正なトークンでリクエスト（異常）
     */
    public function test_store_with_invalid_token_return_401(): void
    {
        // Arrange
        $postData = $this->postData;
        $invalidToken = 'intalid_token';
        // Act
        $response = $this->json('POST', $this->path, $postData, [
                                    'Authorization' => 'Bearer ' . $invalidToken,
                                    'Accept' => 'application/json'
                                ]);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 有効期限切れトークンでリクエスト（異常）
     */
    public function test_store_with_expired_token_return_401(): void
    {
        // Arrange
        $postData = $this->postData;
        DB::table('personal_access_tokens')->where('tokenable_id', $this->user->id)->update([
            'created_at' => Carbon::now()->subDays(1)
        ]);
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(401);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目を空でリクエスト（異常）
     */
    public function test_store_with_empty_required_fields_return_422(): void
    {
        // Arrange
        $postData = [
            'image'   => '',
            'message' => ''
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'image'   => parent::ERROR_MSG['image']['required'],
                    'message' => parent::ERROR_MSG['message']['required'],
                ]);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目をキー無でリクエスト（異常）
     */
    public function test_store_without_required_field_keys_return_422(): void
    {
        // Arrange
        $postData = [
            ''   => file_get_contents(base_path($this->validImagePath)),
            '' => 'test_image'
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'image'   => parent::ERROR_MSG['image']['required'],
                    'message' => parent::ERROR_MSG['message']['required'],
                ]);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * 必須項目をnullでリクエスト（異常）
     */
    public function test_store_with_required_fields_null_return_422(): void
    {
        // Arrange
        $postData = [
            'image'   => null,
            'message' => null
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'image'   => parent::ERROR_MSG['image']['required'],
                    'message' => parent::ERROR_MSG['message']['required'],
                ]);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * "image"を最大サイズでリクエスト（正常）
     */
    public function test_store_with_image_maxsize_return_201(): void
    {
        // Arrange
        $postData = [
            'image'   => file_get_contents(base_path('最大サイズの画像パス')),
            'message' => 'test_image'
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        $post = Post::where('user_id', $this->user->user_id)->first();
        // Assert
        $response->assertStatus(201)
                 ->assertExactJson(['post_id' => $post->id]);
        $this->assertEquals($postData['message'], $post->message);
        Storage::disk('local')->assertExists("images/{$post->id}.jpeg");
    }

    /**
     * "image"を最大サイズ超過でリクエスト（異常）
     */
    public function test_store_with_image_ovresize_return_422(): void
    {
        // Arrange
        $postData = [
            'image'   => file_get_contents(base_path('最大サイズ超過の画像パス')),
            'message' => 'test_image'
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'image' => parent::ERROR_MSG['image']['size'],
                ]);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }

    /**
     * "image"を無効な画像形式のエンコード文字列でリクエスト（異常）
     *
     * @dataProvider providerInvalidImageTypesPath
     */
    public function test_store_with_invalid_image_type_return_422($invalidImagePath): void
    {
        // Arrange
        $postData = [
            'image'   => file_get_contents(base_path($invalidImagePath)),
            'message' => 'test_message'
        ];
        // Act
        $response = $this->json('POST', $this->path, $postData, $this->headers);
        // Assert
        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                    'image'   => parent::ERROR_MSG['image']['fmt'],
                ]);
        $this->assertDatabaseMissing('posts', ['user_id' => $this->user->user_id]);
    }
}
