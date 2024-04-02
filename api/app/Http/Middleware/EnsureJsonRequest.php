<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // リクエストヘッダーのContent-Typeをチェック
        if ($request->header('Content-Type') != 'application/json') {
            return response()->noContent(400);
        }

        // リクエストボディがJSON形式かチェック
        json_decode($request->getContent());
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->noContent(400);
        }

        return $next($request);
    }
}
