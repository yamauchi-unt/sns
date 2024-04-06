<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // ユーザ新規登録
    public function register(RegisterUserRequest $request)
    {
        $validated = $request->validated();
        $user = User::register($validated);

        return response()->noContent(201);
    }

    // プロフィール取得
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user_id' => $user->user_id,
            'user_name'=> $user->user_name,
        ], 200);
    }
}
