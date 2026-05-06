<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\MiddlewareInterface;
use App\Http\Responses\ApiResponse;

class AdminMiddleware implements MiddlewareInterface
{
    /**
     * Kiem tra user co phai admin khong
     * Neu khong phai -> tra 403 Forbidden
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            return ApiResponse::forbidden('Ban khong co quyen truy cap. Chi danh cho Admin.');
        }

        return $next($request);
    }
}
