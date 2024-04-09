<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * リクエストのContent-Typeチェック
 */
class RequestContentTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 正常に登録される有効なユーザ情報
     */
    protected function createTestUser()
    {
        return [
            'user_id' => 'testUser',
            'user_name' => 'Test User',
            'password' => 'password',
        ];
    }

    /**
     * リクエストのContent-Typeチェック
     * Content-Typeはapplication/json、ボディは有効の場合、201
     */
    public function test_contenttype_json_body_valid_return_201()
    {
        // Arrange
        $userData = $this->createTestUser();
        // Act
        $response = $this->json('POST', '/api/users', $userData, ['Content-Type' => 'application/json']);
        // Assert
        $response->assertStatus(201);
    }

    /**
     * リクエストのContent-Typeチェック
     * Content-Typeは指定なし、ボディは有効の場合、400
     */
    public function test_contenttype_none_body_valid_return_400()
    {
        // Arrange
        $userData = $this->createTestUser();
        // Act
        $response = $this->post('/api/users', $userData);
        // Assert
        $response->assertStatus(400);
    }

    /**
     * リクエストのContent-Typeチェック
     * Content-Typeはapplication/json、ボディは空の場合、400
     */
    public function test_contenttype_json_body_none_return_400()
    {
        // Act
        $response = $this->withHeaders(['Content-Type' => 'application/json'])
                         ->post('/api/users');
        // Assert
        $response->assertStatus(400);
    }

    /**
     * リクエストのContent-Typeチェック
     * JContent-Typeはapplication/json、ボディはXML形式の場合、400
     */
    public function test_contenttype_json_body_xml_return_400()
    {
        // Arrange
        $xmlData = '<?xml version="1.0" encoding="UTF-8"?><user><user_id>testUser</user_id><user_name>Test User</user_name><password>password</password></user>';
        // Act
        $response = $this->call('POST', '/api/users', [], [], [], ['CONTENT_TYPE' => 'application/json'], $xmlData);
        // Assert
        $response->assertStatus(400);
    }

    /**
     * 無効なContent-Type
     */
    public static function providerInvalidContentTypes()
    {
        return [
            ['multipart/form-data'],
            ['application/x-www-form-urlencoded'],
            ['text/plain'],
            ['application/javascript'],
            ['text/html'],
            ['application/xml'],
        ];
    }

    /**
     * リクエストのContent-Typeチェック
     * Content-Typeは無効(application/json以外)、ボディは有効の場合、400
     *
     * @dataProvider providerInvalidContentTypes
     */
    public function test_contenttypes_Invalid_body_valid_return_400($contentType)
    {
        // Arrange
        $userData = $this->createTestUser();
        // Act
        $response = $this->withHeaders(['Content-Type' => $contentType])
                         ->post('/api/users', $userData);
        // Assert
        $response->assertStatus(400);
    }

}
