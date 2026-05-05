<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler
{
    /**
     * Render exception thanh JSON response
     */
    public static function render(Throwable $e, $request): ?JsonResponse
    {
        // Chi xu ly API requests
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        return self::renderApiException($e);
    }

    /**
     * Chuyen doi exception thanh JSON response 
     */
    private static function renderApiException(Throwable $e): JsonResponse
    {
        // --- 401 Unauthorized ---
        // Khi chua dang nhap hoac token khong hop le
        if ($e instanceof AuthenticationException) {
            return self::jsonError('Chua xac thuc. Vui long dang nhap', 401);
        }

        // --- 404 Not Found ---
        // Khi goi findOrFail() ma khong tim thay record
        if ($e instanceof ModelNotFoundException) {
            // Lay ten Model (VD: "Order", "User")
            $model = class_basename($e->getModel());
            return self::jsonError("{$model} khong ton tai", 404);
        }

        // --- 404 Not Found ---
        // Khi URL khong khop voi bat ky route nao
        if ($e instanceof NotFoundHttpException) {
            return self::jsonError('Khong tim thay duong dan nay', 404);
        }

        // --- 405 Method Not Allowed ---
        // Khi goi sai HTTP method (VD: GET thay vi POST)
        if ($e instanceof MethodNotAllowedHttpException) {
            return self::jsonError('Phuong thuc HTTP khong duoc ho tro cho duong dan nay', 405);
        }

        // --- 422 Unprocessable Entity ---
        // Khi validation that bai (FormRequest hoac Validator)
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Du lieu khong hop le',
                'errors'  => $e->errors(),
            ], 422);
        }

        // --- HTTP Exception chung (403, 429, etc.) ---
        if ($e instanceof HttpException) {
            return self::jsonError(
                $e->getMessage() ?: 'Loi HTTP',
                $e->getStatusCode()
            );
        }

        // --- 500 Internal Server Error ---
        // Moi loi khac (bug, DB mat ket noi, loi khong luong truoc)
        $message = config('app.debug')
            ? $e->getMessage()          // Development: hien chi tiet
            : 'Loi he thong noi bo';    // Production: an chi tiet

        return self::jsonError($message, 500);
    }

    /**
     * Helper: Tao JSON error response voi format thong nhat
     */
    private static function jsonError(string $message, int $code): JsonResponse
    {
        return response()->json([
            'success'    => false,
            'message'    => $message,
            'error_code' => $code,
        ], $code);
    }
}
