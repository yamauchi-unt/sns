<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthTokenController extends Controller
{
    // トークン取得
    public function store(LoginRequest $request)
    {
        $validated = $request->validated();

        if (Auth::attempt($validated)) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json(['token' => $token], 200);

        } else {
            return response()->noContent(401);
        }
    }

    // トークン削除
    public function destroy()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response()->noContent(204);
    }
}
