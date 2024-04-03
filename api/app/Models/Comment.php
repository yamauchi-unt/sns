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
