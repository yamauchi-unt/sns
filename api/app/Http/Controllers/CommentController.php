<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Comment;

class CommentController extends Controller
{
    // コメント取得
    public function index($post_id)
    {
        $comments = Comment::index($post_id);

        return response()->json($comments, 200);
    }

    // コメント送信
    public function store(StoreCommentRequest $request, $post_id)
    {
        $validated = $request->validated();

        $comment = Comment::store($validated, $post_id);

        return response()->json(['comment_id' => $comment->id], 201);
    }

    // コメント削除
    public function destroy($comment_id)
    {
        // 認証ユーザのユーザID取得
        // $currentUserId = auth()->id();
        $currentUserId = 'test1';
        $result = Comment::deleteIfAuthorized($comment_id, $currentUserId);

        switch ($result) {
            case '204':
                return response()->noContent(204);

            case '404':
                return response()->noContent(404);

            case '403':
                return response()->noContent(403);
        }
    }
}
