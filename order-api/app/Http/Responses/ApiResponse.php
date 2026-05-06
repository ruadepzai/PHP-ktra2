<?php
// ============================================================================
// FILE: app/Http/Responses/ApiResponse.php
// TV2 — Static Factory Pattern
// ============================================================================
//
// 📖 STATIC FACTORY PATTERN LÀ GÌ?
// -----------------------------------
// Thay vì mỗi controller tự viết code tạo JSON response (dễ sai format),
// ta tạo 1 class chuyên biệt chứa các static methods để tạo response.
//
//   ❌ KHÔNG NÊN (mỗi controller tự viết):
//       return response()->json(['success' => true, 'data' => $data], 200);
//       // Nếu 10 controller viết 10 kiểu → không nhất quán
//
//   ✅ NÊN DÙNG (gọi ApiResponse):
//       return ApiResponse::success($data, 'Thành công');
//       // 10 controller gọi cùng 1 chỗ → format luôn nhất quán
//       // Muốn đổi format → sửa 1 file = áp dụng toàn bộ
//
// 📖 STATIC METHOD LÀ GÌ?
// -------------------------
// - Method thường: cần tạo object trước → $obj = new ApiResponse(); $obj->success();
// - Static method: gọi TRỰC TIẾP qua tên class → ApiResponse::success();
//   (Không cần "new", không cần tạo object)
//
// 🎯 FORMAT JSON CHUẨN CỦA DỰ ÁN:
// {
//     "success": true/false,     ← Thành công hay thất bại
//     "message": "Mô tả...",     ← Mô tả kết quả bằng text
//     "data": { ... } hoặc null, ← Dữ liệu trả về (null khi lỗi)
//     "code": 200                ← HTTP status code (200, 201, 400, 401...)
// }
// ============================================================================

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Class ApiResponse — Static Factory Pattern
 *
 * 📌 DANH SÁCH 8 STATIC METHODS:
 *   1. success()      → 200 OK (lấy dữ liệu thành công)
 *   2. created()      → 201 Created (tạo mới thành công)
 *   3. error()        → 400 Bad Request (lỗi chung)
 *   4. notFound()     → 404 Not Found (không tìm thấy)
 *   5. unauthorized() → 401 Unauthorized (chưa đăng nhập)
 *   6. forbidden()    → 403 Forbidden (không có quyền)
 *   7. serverError()  → 500 Internal Server Error (lỗi server)
 *   8. validation()   → 422 Unprocessable Entity (lỗi validation)
 */
class ApiResponse
{
    // =========================================================================
    // 1. SUCCESS — Thành công (HTTP 200)
    // =========================================================================
    /**
     * Response thành công mặc định.
     *
     * 📌 SỬ DỤNG KHI:
     *   - Lấy danh sách đơn hàng: ApiResponse::success($orders, 'Lấy danh sách thành công')
     *   - Xem chi tiết đơn hàng:  ApiResponse::success($order, 'Chi tiết đơn hàng')
     *   - Đăng nhập thành công:   ApiResponse::success(['token' => $token], 'Đăng nhập thành công')
     *
     * 📖 GIẢI THÍCH THAM SỐ:
     * @param  mixed   $data     Dữ liệu trả về — có thể là object, array, collection, hoặc null
     * @param  string  $message  Thông báo mô tả — mặc định "Thành công"
     * @param  int     $code     HTTP status code — mặc định 200
     * @return \Illuminate\Http\JsonResponse  Response JSON format chuẩn
     */
    public static function success($data = null, string $message = 'Thành công', int $code = 200): JsonResponse
    {
        // response()->json() là helper của Laravel, tự động:
        // - Set header Content-Type: application/json
        // - Chuyển array PHP thành chuỗi JSON
        // - Gán HTTP status code (200, 201, ...)
        return response()->json([
            'success' => true,        // ← Luôn là true khi thành công
            'message' => $message,    // ← VD: "Lấy danh sách đơn hàng thành công"
            'data'    => $data,       // ← Dữ liệu thực tế (đơn hàng, user, token...)
            'code'    => $code,       // ← HTTP status code (trùng với status bên ngoài)
        ], $code);  // ← Tham số thứ 2 = HTTP status code thực tế
    }

    // =========================================================================
    // 2. CREATED — Tạo mới thành công (HTTP 201)
    // =========================================================================
    /**
     * Response tạo mới thành công.
     *
     * 📌 SỬ DỤNG KHI:
     *   - Tạo đơn hàng:    ApiResponse::created(new OrderResource($order), 'Tạo đơn hàng thành công')
     *   - Đăng ký tài khoản: ApiResponse::created($user, 'Đăng ký thành công')
     *
     * 📖 TẠI SAO 201 CHỨ KHÔNG PHẢI 200?
     *   HTTP 200 = "OK, tôi đã xử lý xong"
     *   HTTP 201 = "OK, tôi đã TẠO MỚI một resource" → cụ thể hơn, đúng chuẩn RESTful
     */
    public static function created($data = null, string $message = 'Tạo thành công'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'code'    => 201,
        ], 201);  // ← 201 Created
    }

    // =========================================================================
    // 3. ERROR — Lỗi chung (HTTP 400 hoặc custom)
    // =========================================================================
    /**
     * Response lỗi chung.
     *
     * 📌 SỬ DỤNG KHI:
     *   - Hủy đơn đã giao:   ApiResponse::error('Không thể hủy đơn đang giao', 400)
     *   - Sửa đơn đã xác nhận: ApiResponse::error('Chỉ sửa được khi pending', 422)
     *   - Validation lỗi:     ApiResponse::error('Dữ liệu sai', 422, $validator->errors())
     *
     * 📖 THAM SỐ $errors:
     *   - Khi validation thất bại, $errors chứa danh sách lỗi từng field:
     *     { "email": ["Email không đúng"], "name": ["Tên là bắt buộc"] }
     *   - Khi lỗi thường (không phải validation), $errors = null → không có key "errors"
     */
    public static function error(string $message = 'Có lỗi xảy ra', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,       // ← Luôn là false khi lỗi
            'message' => $message,
            'data'    => null,        // ← Khi lỗi thì không có data
            'code'    => $code,
        ];

        // Chỉ thêm key "errors" khi có lỗi validation
        // → Tránh trả về "errors": null (thừa thông tin)
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    // =========================================================================
    // 4. NOT FOUND — Không tìm thấy (HTTP 404)
    // =========================================================================
    /**
     * Response không tìm thấy tài nguyên.
     *
     * 📌 SỬ DỤNG KHI:
     *   - Đơn hàng không tồn tại:  ApiResponse::notFound('Đơn hàng không tồn tại')
     *   - User không tìm thấy:     ApiResponse::notFound('Người dùng không tồn tại')
     *
     * 📖 KHI NÀO XẢY RA 404?
     *   VD: Client gọi GET /api/orders/9999 nhưng đơn hàng #9999 không có trong DB
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

    // =========================================================================
    // 5. UNAUTHORIZED — Chưa xác thực (HTTP 401)
    // =========================================================================
    /**
     * Response chưa xác thực — "Bạn là AI?"
     *
     * 📌 SỬ DỤNG KHI (bởi JwtAuthMiddleware — TV5):
     *   - Không gửi token:        ApiResponse::unauthorized('Token không được cung cấp')
     *   - Token hết hạn:          ApiResponse::unauthorized('Token đã hết hạn')
     *   - Token bị sửa đổi:      ApiResponse::unauthorized('Token không hợp lệ')
     *
     * 📖 PHÂN BIỆT 401 vs 403:
     *   401 = "Tôi không biết bạn là ai" (chưa đăng nhập)
     *   403 = "Tôi biết bạn là ai, nhưng bạn không được phép" (đã đăng nhập nhưng không có quyền)
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

    // =========================================================================
    // 6. FORBIDDEN — Không có quyền (HTTP 403)
    // =========================================================================
    /**
     * Response không có quyền truy cập — "Bạn KHÔNG ĐƯỢC PHÉP!"
     *
     * 📌 SỬ DỤNG KHI (bởi OrderOwnerMiddleware — TV5):
     *   - User A cố xem đơn hàng của User B:
     *     ApiResponse::forbidden('Bạn không có quyền truy cập đơn hàng này')
     *
     * 📖 VÍ DỤ THỰC TẾ:
     *   Bạn đã đăng nhập Shopee (401 OK), nhưng cố mở đơn hàng của người khác
     *   → Shopee trả về 403: "Bạn không có quyền xem đơn này"
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

    // =========================================================================
    // 7. SERVER ERROR — Lỗi máy chủ (HTTP 500)
    // =========================================================================
    /**
     * Response lỗi máy chủ — lỗi hệ thống không lường trước.
     *
     * 📌 SỬ DỤNG KHI:
     *   - Database mất kết nối:  ApiResponse::serverError('Lỗi kết nối database')
     *   - Bug trong code:        ApiResponse::serverError('Lỗi hệ thống')
     *
     * ⚠️ QUAN TRỌNG: Trong production, KHÔNG hiển thị chi tiết lỗi cho client
     *   (vì hacker có thể lợi dụng thông tin lỗi để tấn công)
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

    // =========================================================================
    // 8. VALIDATION — Lỗi validate dữ liệu (HTTP 422)
    // =========================================================================
    /**
     * Response lỗi validation — dữ liệu đầu vào không hợp lệ.
     *
     * 📌 SỬ DỤNG BỞI: AuthController (TV5) khi đăng ký/đăng nhập
     *
     * 📌 VÍ DỤ:
     *   $validator = Validator::make($request->all(), ['email' => 'required|email']);
     *   if ($validator->fails()) {
     *       return ApiResponse::validation($validator->errors());
     *   }
     *
     * 📖 RESPONSE TRẢ VỀ:
     *   {
     *     "success": false,
     *     "message": "Dữ liệu không hợp lệ",
     *     "data": null,
     *     "errors": {                    ← Danh sách lỗi theo từng field
     *       "email": ["Email là bắt buộc"],
     *       "password": ["Mật khẩu phải có ít nhất 6 ký tự"]
     *     },
     *     "code": 422
     *   }
     *
     * @param  mixed   $errors   Chi tiết lỗi (MessageBag từ $validator->errors())
     * @param  string  $message  Thông báo lỗi chung
     * @return \Illuminate\Http\JsonResponse
     */
    public static function validation($errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => null,
            'errors'  => $errors,     // ← Luôn có key "errors" cho validation
            'code'    => 422,
        ], 422);  // ← 422 Unprocessable Entity
    }
}
