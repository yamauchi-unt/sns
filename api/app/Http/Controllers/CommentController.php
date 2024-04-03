<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Comment;

class CommentController extends Controller
{
    //コメント送信
    public function store(StoreCommentRequest $request, $post_id)
    {
        $validated = $request->validated();

        $comment = Comment::store($validated, $post_id);

        return response()->json(['comment_id' => $comment->id], 201);
    }
}
