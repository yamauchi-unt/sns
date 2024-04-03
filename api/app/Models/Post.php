<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'message',
    ];

    // 投稿を登録
    public static function store($validated)
    {
        $post = Post::create([
            // 'user_id' => auth()->user()->id,
            'user_id' => 'abcd1234',
            'message' => $validated['message'],
        ]);

        return $post;
    }

}
