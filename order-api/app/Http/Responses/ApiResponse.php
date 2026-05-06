<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ApiResponse
 *
 * Lớp tiện ích cung cấp các static factory methods để tạo JSON response
 * với format chuẩn, nhất quán cho toàn bộ API.
 *
 * Design Pattern: Static Factory Method
 * - Tất cả method đều là static — gọi trực tiếp ApiResponse::success($data)
 * - Tuân thủ DRY principle: mọi controller gọi chung 1 nơi, không tự viết response
 * - Nếu cần thay đổi format JSON → sửa 1 file = áp dụng toàn bộ hệ thống
 *
 * Format JSON chuẩn:
 * {
 *     "success": true|false,   // boolean - kết quả thành công hay thất bại
 *     "message": "...",        // string - mô tả kết quả
 *     "data": { ... },         // mixed - dữ liệu trả về (null khi lỗi)
 *     "code": 200              // integer - HTTP status code
 * }
 *
 * Khi có validation errors, thêm key "errors":
 * {
 *     "success": false,
 *     "message": "Dữ liệu không hợp lệ",
 *     "data": null,
 *     "errors": { "field": ["lỗi 1", "lỗi 2"] },
 *     "code": 422
 * }
 *
 * @package App\Http\Responses
 */
class ApiResponse
{
    /**
     * Response thành công mặc định.
     *
     * Sử dụng khi: Lấy danh sách đơn hàng, xem chi tiết đơn hàng,
     * cập nhật đơn hàng thành công, đăng nhập thành công, refresh token...
     *
     * @param  mixed   $data     Dữ liệu trả về (object, array, collection, null)
     * @param  string  $message  Thông báo mô tả kết quả
     * @param  int     $code     HTTP status code (mặc định 200)
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::success($orders, 'Lấy danh sách đơn hàng thành công')
     * @example ApiResponse::success(['token' => $token], 'Đăng nhập thành công')
     */
    public static function success($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => $code,
        ], $code);
    }

    /**
     * Response tạo mới thành công (HTTP 201 Created).
     *
     * Sử dụng khi: Tạo đơn hàng mới thành công, đăng ký tài khoản thành công.
     *
     * @param  mixed   $data     Dữ liệu resource vừa tạo
     * @param  string  $message  Thông báo mô tả kết quả
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::created(new OrderResource($order), 'Tạo đơn hàng thành công')
     * @example ApiResponse::created($user, 'Đăng ký tài khoản thành công')
     */
    public static function created($data = null, string $message = 'Tạo thành công'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => 201,
        ], 201);
    }

    /**
     * Response lỗi chung (HTTP 400 Bad Request hoặc custom code).
     *
     * Sử dụng khi: Hủy đơn hàng đã giao, sửa đơn đã xác nhận,
     * chuyển trạng thái không hợp lệ, validation lỗi (422)...
     *
     * Khi $errors không null (validation errors), key "errors" sẽ được
     * thêm vào JSON response để client hiển thị lỗi từng field.
     *
     * @param  string  $message  Thông báo lỗi
     * @param  int     $code     HTTP status code (mặc định 400)
     * @param  mixed   $errors   Chi tiết lỗi validation (MessageBag hoặc array)
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::error('Không thể hủy đơn hàng đang giao', 400)
     * @example ApiResponse::error('Dữ liệu không hợp lệ', 422, $validator->errors())
     */
    public static function error(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'data'    => null,
            'code'    => $code,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Response không tìm thấy tài nguyên (HTTP 404 Not Found).
     *
     * Sử dụng khi: Đơn hàng không tồn tại, user không tồn tại,
     * truy cập resource với ID không hợp lệ.
     *
     * @param  string  $message  Thông báo lỗi
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::notFound('Không tìm thấy đơn hàng')
     * @example ApiResponse::notFound('Đơn hàng #123 không tồn tại')
     */
    public static function notFound(string $message = 'Không tìm thấy tài nguyên'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'code'    => 404,
        ], 404);
    }

    /**
     * Response chưa xác thực (HTTP 401 Unauthorized).
     *
     * Sử dụng khi: Token JWT sai, hết hạn, không có token,
     * token bị blacklist sau khi logout.
     *
     * @param  string  $message  Thông báo lỗi
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::unauthorized('Token không hợp lệ')
     * @example ApiResponse::unauthorized('Token đã hết hạn, vui lòng đăng nhập lại')
     */
    public static function unauthorized(string $message = 'Chưa xác thực'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'code'    => 401,
        ], 401);
    }

    /**
     * Response không có quyền truy cập (HTTP 403 Forbidden).
     *
     * Sử dụng khi: User cố xem/sửa/xóa đơn hàng của người khác,
     * truy cập tài nguyên không thuộc quyền sở hữu.
     *
     * @param  string  $message  Thông báo lỗi
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::forbidden('Bạn không có quyền truy cập đơn hàng này')
     * @example ApiResponse::forbidden('Không có quyền thực hiện thao tác này')
     */
    public static function forbidden(string $message = 'Không có quyền truy cập'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'code'    => 403,
        ], 403);
    }

    /**
     * Response lỗi máy chủ (HTTP 500 Internal Server Error).
     *
     * Sử dụng khi: Exception không xử lý được, lỗi database,
     * lỗi service bên thứ ba, lỗi hệ thống không lường trước.
     *
     * @param  string  $message  Thông báo lỗi
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::serverError('Lỗi kết nối database')
     * @example ApiResponse::serverError('Không thể xử lý yêu cầu, vui lòng thử lại sau')
     */
    public static function serverError(string $message = 'Lỗi máy chủ'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'code'    => 500,
        ], 500);
    }

    /**
     * Response lỗi validation (HTTP 422 Unprocessable Entity).
     *
     * Sử dụng khi: Form validation thất bại, dữ liệu đầu vào không hợp lệ.
     *
     * @param  mixed   $errors   Chi tiết lỗi validation (MessageBag hoặc array)
     * @param  string  $message  Thông báo lỗi
     * @return \Illuminate\Http\JsonResponse
     *
     * @example ApiResponse::validation($validator->errors())
     */
    public static function validation($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,
            'code'    => 422,
        ], 422);
    }
}
