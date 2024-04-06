<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    //投稿
    public function store(StorePostRequest $request)
    {
        // バリデーション済データ取得
        $validated = $request->validated();
        // 認証ユーザのユーザID取得
        $userId = Auth::user()->user_id;

        try {
            DB::beginTransaction();

            // 投稿テーブルへ登録
            $post = Post::store($validated, $userId);

            // 画像ファイル名を変更しパス取得
            $imagePath = "images/{$post->id}.jpeg";
            // デコードされた画像データをストレージに保存
            Storage::put($imagePath, $validated['decoded_image']);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return response()->json(['post_id' => $post->id], 201);
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
        $userId = Auth::user()->user_id;
        $myposts = Post::indexMyPosts($userId);

        return response()->json($myposts, 200);
    }

    // 投稿1件取得
    public function show($post_id)
    {
        $post = Post::show($post_id);
        // $userId = auth()->id();
        $userId = 'test1';

        if($post === '404') {
            return response()->noContent(404);
        }

        // 自分の投稿か判定
        $post->mine_frg = $post->user_id === $userId;

        // 画像取得しエンコード
        $imagePath = "images/{$post->id}.jpeg";
        if(Storage::exists($imagePath)) {
            $imageData = Storage::get($imagePath);
            $post->image = base64_encode($imageData);
        } else {
            $post->image = null;
        }

        return response()->json($post, 200);
    }

    // 投稿1件削除
    public function destroy($post_id)
    {
        // 認証ユーザのユーザID取得
        // $currentUserId = auth()->user()->id;
        $currentUserId = 'test1';

        DB::beginTransaction();

        try {
            $result = Post::deleteIfAuthorized($post_id, $currentUserId);

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
