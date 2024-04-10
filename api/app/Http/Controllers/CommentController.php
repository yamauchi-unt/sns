<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // コメント送信
    public function store(StoreCommentRequest $request, Post $post)
    {
        $userId = Auth::user()->user_id;
        $validated = $request->validated();

        $comment = Comment::store($post->id, $userId, $validated);

        return response()->json(['comment_id' => $comment->id], 201);
    }

    // コメント取得
    public function index($postId)
    {
        $userId = Auth::user()->user_id;
        $comments = Comment::index($postId, $userId);

        return response()->json($comments, 200);
    }

    // コメント1件削除
    public function destroy(Comment $comment)
    {
        // 削除権限があるかチェック、権限なければ403
        $this->authorize('delete', $comment);

        // コメント削除
        $comment->delete();

        return response()->noContent(204);
    }
}
