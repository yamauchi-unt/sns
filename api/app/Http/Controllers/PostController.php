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
    public function show($postId)
    {
        $userId = Auth::user()->user_id;
        $imageService = app(ImageService::class);
        $post = Post::showWithImage($postId, $userId, $imageService);

        // 投稿詳細が取得できなかった場合404
        if($post === null) {
            return response()->noContent(404);
        }

        return response()->json($post, 200);
    }

    // 投稿1件削除
    public function destroy($post_id)
    {
        $userId = Auth::user()->user_id;

        DB::beginTransaction();

        try {
            $result = Post::deleteIfAuthorized($post_id, $userId);

            switch ($result) {
                case '204':
                    $imagePath = "images/{$post_id}.jpeg";
                    if (Storage::exists($imagePath)) {
                        Storage::delete($imagePath);
                    }
                    DB::commit();
                    return response()->noContent(204);

                case '403':
                    DB::rollBack();
                    return response()->noContent(403);

                case '404':
                    DB::rollBack();
                    return response()->noContent(404);
                }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
