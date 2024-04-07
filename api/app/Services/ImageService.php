<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageService
{
    protected $baseImagePath;
    protected $imageFormat;

    public function __construct()
    {
        $this->baseImagePath = env('IMAGE_BASE_PATH', 'images');
        $this->imageFormat = env('IMAGE_FORMAT', 'jpeg');
    }

    // 画像ファイルのパス取得する
    public function getImagePath(string $postId)
    {
        return "{$this->baseImagePath}/{$postId}.{$this->imageFormat}";
    }

    // 画像ファイルを指定パスへ保存する
    public function saveImage(string $postId, string $imageData)
    {
        $imagePath = $this->getImagePath($postId);
        Storage::put($imagePath, $imageData);
    }

    // Base64エンコードされた画像データをデコードする
    public static function decodeBase64(string $encodedImageData): string
    {
        $pattern = '/^data:image\/[a-zA-Z]+;base64,/';
        $pureBase64Data = preg_replace($pattern, '', $encodedImageData);

        return base64_decode($pureBase64Data);
    }

    // 画像データをBase64エンコードで取得する
    public function getEncodedImage(string $postId)
    {
        $imagePath = $this->getImagePath($postId);

        if (Storage::exists($imagePath)) {
            $imageData = Storage::get($imagePath);
            return base64_encode($imageData);
        }
        return null;
    }

    // 画像データを削除する
    public function deleteImage(string $postId)
    {
        $imagePath = $this->getImagePath($postId);

        if (Storage::exists($imagePath)) {
            Storage::delete($imagePath);
        }
    }
}
