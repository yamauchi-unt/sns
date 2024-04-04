<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 投稿
Route::post('/posts', [PostController::class, 'store'])
    ->middleware('ensure.json');

// 投稿取得
Route::get('/posts', [PostController::class, 'index']);

// 自分の投稿取得
Route::get('/myposts', [PostController::class, 'indexMyPosts']);

// 投稿1件取得
Route::get('/posts/{post_id}', [PostController::class,'show'])
    ->whereNumber('post_id');

// コメント取得
Route::get('/posts/{post_id}/comments', [CommentController::class, 'index'])
    ->whereNumber('post_id');

// コメント送信
Route::post('/posts/{post_id}/comments', [CommentController::class, 'store'])
    ->whereNumber('post_id')
    ->middleware('ensure.json');

// コメント削除
Route::delete('/comments/{comment_id}', [CommentController::class, 'destroy'])
    ->whereNumber('comment_id');
