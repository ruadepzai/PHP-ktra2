<?php

namespace App\Contracts;

use Closure;
use Illuminate\Http\Request;

/**
 * Interface MiddlewareInterface
 *
 * Hợp đồng (contract) buộc mọi middleware custom phải implement method handle().
 *
 * Trong Laravel 11+, middleware không cần extends bất kỳ class nào,
 * chỉ cần một class có method handle(). Interface này đảm bảo tất cả
 * custom middleware trong dự án tuân theo cùng một chuẩn chung (contract),
 * giúp code nhất quán và dễ bảo trì.
 *
 * Design Pattern: Interface (OOP Contract)
 * - Interface chỉ chứa method signatures (hợp đồng), không có code thực thi.
 * - Khác với Abstract Class có thể có cả abstract methods lẫn concrete methods.
 * - Một class có thể implements nhiều Interface nhưng chỉ extends được 1 class.
 *
 * Các middleware sử dụng interface này:
 * - JwtAuthMiddleware (TV5): Xác thực JWT token → 401 nếu không hợp lệ
 * - OrderOwnerMiddleware (TV5): Kiểm tra quyền sở hữu đơn hàng → 403
 * - CorsMiddleware (TV5): Thêm CORS headers cho cross-origin requests
 *
 * @package App\Contracts
 */
interface MiddlewareInterface
{
    /**
     * Xử lý request HTTP đến.
     *
     * Mỗi middleware nhận request, thực hiện logic kiểm tra/xử lý,
     * sau đó quyết định cho request đi tiếp ($next) hoặc trả về response lỗi.
     *
     * @param  \Illuminate\Http\Request  $request  Request HTTP đến
     * @param  \Closure  $next  Callback để chuyển request sang middleware/controller tiếp theo
     * @return mixed  Response HTTP (JsonResponse hoặc tiếp tục pipeline)
     */
    public function handle(Request $request, Closure $next): mixed;
}
