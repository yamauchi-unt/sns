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
}
