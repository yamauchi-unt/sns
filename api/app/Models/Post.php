<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

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
    public static function store(array $validated, string $userId)
    {
        $post = self::create([
            'user_id' => $userId,
            'message' => $validated['message'],
        ]);

        return $post;
    }

    // 投稿取得
    public static function index()
    {
        $posts = Post::orderBy('id','desc')->paginate(10);

        return $posts;
    }

    // 自分の投稿取得
    public static function indexMyPosts($userId)
    {
        $myposts = Post::where('user_id', $userId)
            ->orderBy('id','desc')
            ->paginate(10);

        return $myposts;
    }


    // 投稿1件取得
    public static function show($post_id)
    {
        $post = Post::withCount('comments')->find($post_id);

        return $post;
    }

    // 投稿1件削除
    public static function deleteIfAuthorized($post_id, $user_id)
    {
        $post = Post::find($post_id);

        if (!$post) {
            return '404';
        }
        if ($post->user_id !== $user_id) {
            return '403';
        }

        $post->delete();
        return '204';
    }

}
