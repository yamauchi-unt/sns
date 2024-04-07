<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;

    // 投稿に紐づくユーザのリレーション
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // 投稿に紐づくコメントのリレーション
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'message',
    ];

    // 投稿（登録）
    public static function storeWithImage(string $userId, array $value, ImageService $imageService)
    {
        DB::beginTransaction();
        try {
            // 投稿テーブルへ登録
            $post = self::create([
                'user_id' => $userId,
                'message' => $value['message'],
            ]);

            // 画像を保存
            $imageService->saveImage($post->id, $value['image']);

            DB::commit();
            return $post;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 投稿取得
    public static function index()
    {
        $posts = self::orderBy('id','desc')->paginate(10);

        // paginateのdata部分のデータ構造を書き換え
        $transformedPosts = $posts->getCollection()->map(function ($post) {
            return [
                'id'=> $post->id,
            ];
        });

        // 書き換え実行
        $posts->setCollection($transformedPosts);

        return $posts;
    }

    // 自分の投稿取得
    public static function indexMyposts(string $userId)
    {
        $myposts = self::where('user_id', $userId)
            ->orderBy('id','desc')
            ->paginate(10);

        // paginateのdata部分のデータ構造を書き換え
        $transformedMyposts = $myposts->getCollection()->map(function ($mypost) {
            return [
                'id'=> $mypost->id,
            ];
        });

        // 書き換え実行
        $myposts->setCollection($transformedMyposts);

        return $myposts;
    }


    // 投稿1件取得
    public static function showWithImage(string $postId, string $userId, ImageService $imageService)
    {
        $post = self::withCount('comments')->find($postId);

        if (!$post) {
            return null;
        }

        // データ構造を書き換え
        $transformedPost = [
                'post_id' => $post->id,
                'mine_frg' => $post->user_id === $userId,
                'user_name' => $post->user->user_name,
                'image' => $imageService->getEncodedImage($post->id),
                'message' => $post->message,
                'post_date' => $post->created_at->toDateTimeString(),
                'comment_count' => $post->comments_count,
            ];

        return $transformedPost;
    }

    // 投稿1件削除
    public static function deleteIfAuthorized($post_id, $userId)
    {
        $post = Post::find($post_id);

        if (!$post) {
            return '404';
        }
        if ($userId !== $post->user_id) {
            return '403';
        }

        $post->delete();
        return '204';
    }

}
