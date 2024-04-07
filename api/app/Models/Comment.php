<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    // コメントに紐づくユーザのリレーション
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'comment',
    ];

    // コメント送信（登録）
    public static function store(string $post_id, string $user_id, array $value)
    {
        $comment = self::create([
            'post_id' => $post_id,
            'user_id' => $user_id,
            'comment' => $value['comment'],
        ]);

        return $comment;
    }

    // コメント取得
    public static function index(string $post_id, string $user_id)
    {
        $comments = self::with('user')
            ->where('post_id', $post_id)
            ->orderBy('id','desc')
            ->paginate(10);

        $transformedComments = $comments->getCollection()->map(function ($comment) use ($user_id) {
            return [
                'comment_id' => $comment->id,
                'post_id'    => $comment->post_id,
                'mine_frg'   => $comment->user_id === $user_id,
                'user_name'  => $comment->user->user_name,
                'comment'    => $comment->comment,
            ];
        });

        // paginateのdata部分を上で作成した配列に置き換え
        $comments->setCollection($transformedComments);

        return $comments;
    }

    // コメント削除
    public static function deleteIfAuthorized($comment_id, $user_id)
    {
        $comment = Comment::get($comment_id);

        if (!$comment) {
            return '404';
        }

        if ($comment->user_id === $user_id) {
            return '403';
        }

        $comment->delete();
        return '204';
    }
}
