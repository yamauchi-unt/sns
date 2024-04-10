<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //投稿
    public function store(StorePostRequest $request)
    {
        $userId = Auth::user()->user_id;
        $validated = $request->validated();
        $imageService = app(ImageService::class);

        $post = Post::storeWithImage($userId, $validated, $imageService);

        return response()->json(['post_id' => $post->id], 201);
    }

    // 投稿取得
    public function index()
    {
        $posts = Post::index();

        return response()->json($posts, 200);
    }

    // 自分の投稿取得
    public function indexMyposts()
    {
        $userId = Auth::user()->user_id;
        $myposts = Post::indexMyposts($userId);

        return response()->json($myposts, 200);
    }

    // 投稿1件取得
    public function show(Post $post)
    {
        $userId = Auth::user()->user_id;
        $imageService = app(ImageService::class);

        // 投稿1件取得
        $post = Post::showWithImage($post->id, $userId, $imageService);

        return response()->json($post, 200);
    }

    // 投稿1件削除
    public function destroy(Post $post)
    {
        $imageService = app(ImageService::class);

        // 削除権限があるかチェック、権限なければ403
        $this->authorize('delete', $post);

        // 投稿・画像削除
        Post::deleteWithImage($post->id, $imageService);

        return response()->noContent(204);
    }
}
