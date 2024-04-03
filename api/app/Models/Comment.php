<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

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

    // コメント取得
    public static function index($post_id)
    {
        $comments = Comment::where('post_id', $post_id)
            ->orderBy('id','desc')
            ->paginate(10);

        // 認証ユーザのユーザID取得
        // $currentUserId = auth()->id();
        $currentUserId = 'test1';

        // 認証ユーザ自身のコメントか判定し、mine_flagキーを追加
        $comments->getCollection()->transform(function ($comment) use ($currentUserId) {
            $comment->mine_flag = $comment->user_id === $currentUserId;
            return $comment;
        });

        return $comments;
    }

    // コメント送信
    public static function store($validated, $post_id)
    {
        $comment = Comment::create([
            'post_id'=> $post_id,
            // 'user_id' => auth()->user()->id,
            'user_id' => 'test3',
            'comment' => $validated['comment'],
        ]);

        return $comment;
    }
}
