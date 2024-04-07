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
    public function store(StoreCommentRequest $request, $post_id)
    {
        $user_id = Auth::user()->user_id;
        $validated = $request->validated();

        $comment = Comment::store($post_id, $user_id, $validated);

        return response()->json(['comment_id' => $comment->id], 201);
    }

    // コメント取得
    public function index($post_id)
    {
        $user_id = Auth::user()->user_id;
        $comments = Comment::index($post_id, $user_id);

        return response()->json($comments, 200);
    }

    // コメント削除
    public function destroy($comment_id)
    {
        $user_id = Auth::user()->user_id;
        $result = Comment::deleteIfAuthorized($comment_id, $user_id);

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
