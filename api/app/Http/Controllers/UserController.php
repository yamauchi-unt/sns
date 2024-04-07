<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function show()
    {
        $user = Auth::user();

        return response()->json([
            "user_id" => $user->user_id,
            "user_name" => $user->user_name,
        ], 200);
    }

    // プロフィール編集
    public function update(UpdateUserRequest $request)
    {
        $user = Auth::user();
        $validated = $request->safe()->only(['user_name', 'new_password']);

        $user->myprofileUpdate($validated);

        return response()->noContent(200);
    }
}
