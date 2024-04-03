<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //投稿
    public function store(StorePostRequest $request)
    {
        // バリデーション済データ取得
        $validated = $request->validated();

        $storedPostId = null;

        DB::transaction(function () use ($validated, &$storedPostId) {
            // 投稿テーブルへ登録
            $post = Post::store($validated);
            $storedPostId = $post->id;

            // 画像ファイル名を変更しパス取得
            $imagePath = "images/{$storedPostId}.jpeg";

            // デコードされた画像データをストレージに保存
            Storage::put($imagePath, $validated['decoded_image']);
        });

        return response()->json(['post_id' => $storedPostId], 201);
    }

    // 投稿取得
    public function index()
    {
        $posts = Post::index();

        return response()->json($posts, 200);
    }

    // 自分の投稿取得
    public function indexMyPosts()
    {
        $myposts = Post::indexMyPosts();

        return response()->json($myposts, 200);
    }
}
