<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    // コメント送信
    public function store(StoreCommentRequest $request, $postId)
    {
        $userId = Auth::user()->user_id;
        $validated = $request->validated();

        $comment = Comment::store($postId, $userId, $validated);

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
    public function destroy($commentId)
    {
        $userId = Auth::user()->user_id;
        $result = Comment::deleteIfAuthorized($commentId, $userId);

        switch ($result) {
            case '204':
                return response()->noContent(204);
            case '403':
                return response()->noContent(403);
            case '404':
                return response()->noContent(404);
        }
    }
}
