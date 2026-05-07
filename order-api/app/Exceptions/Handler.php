<?php

namespace App\Exceptions;

use Throwable;
use App\Http\Responses\ApiResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler
{
    /**
     * Thêm CORS headers vào response lỗi.
     * Khi exception xảy ra, response có thể bypass CorsMiddleware,
     * nên cần gắn CORS headers trực tiếp tại đây.
     */
    private static function withCorsHeaders($response)
    {
        if ($response) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        }
        return $response;
    }

    public static function renderApi(Throwable $e, $request)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            if ($e instanceof ValidationException) {
                return self::withCorsHeaders(ApiResponse::validation($e->errors(), 'Dữ liệu không hợp lệ'));
            }
            if ($e instanceof NotFoundHttpException) {
                return self::withCorsHeaders(ApiResponse::notFound('Không tìm thấy tài nguyên yêu cầu'));
            }
            if ($e instanceof MethodNotAllowedHttpException) {
                return self::withCorsHeaders(ApiResponse::error('Phương thức không được hỗ trợ', 405));
            }
            if ($e instanceof UnauthorizedHttpException) {
                return self::withCorsHeaders(ApiResponse::unauthorized('Chưa xác thực hoặc token hết hạn'));
            }
            if ($e instanceof AccessDeniedHttpException) {
                return self::withCorsHeaders(ApiResponse::forbidden('Bạn không có quyền thực hiện thao tác này'));
            }
            return self::withCorsHeaders(ApiResponse::serverError('Lỗi hệ thống: ' . $e->getMessage()));
        }
        return null;
    }
}
