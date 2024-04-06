<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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

// 投稿
Route::middleware(['auth:sanctum', 'ensure.json'])
    ->post('/posts', [PostController::class, 'store']);

// 投稿取得
Route::middleware(['auth:sanctum'])
    ->get('/posts', [PostController::class, 'index']);

// 自分の投稿取得
Route::middleware(['auth:sanctum'])
    ->get('/myposts', [PostController::class, 'indexMyPosts']);

// 投稿1件取得
Route::middleware(['auth:sanctum'])
    ->get('/posts/{post_id}', [PostController::class, 'show'])
    ->whereNumber('post_id');

// 投稿1件削除
Route::middleware(['auth:sanctum'])
    ->delete('/posts/{post_id}', [PostController::class, 'destroy'])
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

// ユーザ新規登録
Route::post('/users', [UserController::class, 'register'])
    ->middleware('ensure.json');

// プロフィール取得
Route::middleware('auth:sanctum')
    ->get('/myprofile', [UserController::class, 'show']);

// プロフィール編集
Route::middleware(['auth:sanctum', 'ensure.json'])
    ->patch('/myprofile', [UserController::class, 'update']);

// トークン取得
Route::post('/auth/token', [AuthTokenController::class, 'store'])
    ->middleware('ensure.json');

// トークン削除
Route::middleware('auth:sanctum')
    ->delete('/auth/token', [AuthTokenController::class, 'destroy']);
