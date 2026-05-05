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

/**
 * TV5 — Handler (Error Handler tap trung)
 * 
 * Xu ly TAT CA loi trong ung dung va tra ve JSON format thong nhat
 * 
 * Tai sao can Error Handler tap trung?
 *   - Khong co: Moi controller tu format loi khac nhau -> KHONG NHAT QUAN
 *   - Co:       Moi loi deu tra ve cung 1 format JSON -> CHUYEN NGHIEP
 * 
 * Format loi thong nhat:
 *   {
 *       "success": false,
 *       "message": "Mo ta loi",
 *       "error_code": 404
 *   }
 * 
 * Duoc goi tu bootstrap/app.php -> withExceptions()
 */
class Handler
{
    /**
     * Render exception thanh JSON response
     * Chi xu ly cac request API (url bat dau bang /api/)
     *
     * @param  Throwable  $e        — Exception duoc nem ra
     * @param  mixed      $request  — HTTP Request
     * @return JsonResponse|null    — Tra ve JSON hoac null (de Laravel xu ly mac dinh)
     */
    public static function render(Throwable $e, $request): ?JsonResponse
    {
        // Chi xu ly API requests
        // Request web (Blade) de Laravel xu ly mac dinh (hien trang loi HTML)
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        return self::renderApiException($e);
    }

    /**
     * Chuyen doi exception thanh JSON response voi HTTP status code phu hop
     *
     * @param  Throwable  $e
     * @return JsonResponse
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
        // Trong production: KHONG hien thi chi tiet loi (bao mat)
        // Trong development: hien thi message de debug
        $message = config('app.debug')
            ? $e->getMessage()          // Development: hien chi tiet
            : 'Loi he thong noi bo';    // Production: an chi tiet

        return self::jsonError($message, 500);
    }

    /**
     * Helper: Tao JSON error response voi format thong nhat
     *
     * @param  string  $message  — Mo ta loi
     * @param  int     $code     — HTTP status code
     * @return JsonResponse
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