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
    public static function store(string $postId, string $userId, array $value)
    {
        $comment = self::create([
            'post_id' => $postId,
            'user_id' => $userId,
            'comment' => $value['comment'],
        ]);

        return $comment;
    }

    // コメント取得
    public static function index(string $postId, string $userId)
    {
        $comments = self::with('user')
            ->where('post_id', $postId)
            ->orderBy('id','desc')
            ->paginate(10);

        // paginateのdata部分のデータ構造を書き換え
        $transformedComments = $comments->getCollection()->map(function ($comment) use ($userId) {
            return [
                'comment_id' => $comment->id,
                'post_id'    => $comment->post_id,
                'mine_frg'   => $comment->user_id === $userId,
                'user_name'  => $comment->user->user_name,
                'comment'    => $comment->comment,
            ];
        });

        // 書き換え実行
        $comments->setCollection($transformedComments);

        return $comments;
    }
}
