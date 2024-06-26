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
    ->get('/myposts', [PostController::class, 'indexMyposts']);

// 投稿1件取得
Route::middleware(['auth:sanctum'])
    ->get('/posts/{post}', [PostController::class, 'show'])
    ->whereNumber('post');

// 投稿1件削除
Route::middleware(['auth:sanctum'])
    ->delete('/posts/{post}', [PostController::class, 'destroy'])
    ->whereNumber('post');

// コメント送信
Route::middleware(['auth:sanctum', 'ensure.json'])
    ->post('/posts/{post}/comments', [CommentController::class, 'store'])
    ->whereNumber('post');

// コメント取得
Route::middleware(['auth:sanctum'])
    ->get('/posts/{post}/comments', [CommentController::class, 'index'])
    ->whereNumber('post');

// コメント1件削除
Route::middleware(['auth:sanctum'])
    ->delete('/comments/{comment}', [CommentController::class, 'destroy'])
    ->whereNumber('comment');

// ユーザ新規登録
Route::middleware(['ensure.json'])
    ->post('/users', [UserController::class, 'register']);

// プロフィール取得
Route::middleware(['auth:sanctum'])
    ->get('/myprofile', [UserController::class, 'show']);

// プロフィール編集
Route::middleware(['auth:sanctum', 'ensure.json'])
    ->patch('/myprofile', [UserController::class, 'update']);

// トークン取得
Route::middleware(['ensure.json'])
    ->post('/auth/token', [AuthTokenController::class, 'store']);

// トークン削除
Route::middleware(['auth:sanctum'])
    ->delete('/auth/token', [AuthTokenController::class, 'destroy']);
