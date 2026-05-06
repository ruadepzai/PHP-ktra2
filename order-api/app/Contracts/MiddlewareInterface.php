<?php
// ============================================================================
// FILE: app/Contracts/MiddlewareInterface.php
// TV2 — Interface (OOP Contract)
// ============================================================================
//
// 📖 INTERFACE LÀ GÌ?
// --------------------
// Interface giống như một "hợp đồng" (contract) trong lập trình.
// Nó chỉ khai báo TÊN METHOD và THAM SỐ, nhưng KHÔNG viết code bên trong.
// Bất kỳ class nào "implements" interface này đều BẮT BUỘC phải viết code
// cho tất cả các method đã khai báo trong interface.
//
// 🔑 SO SÁNH INTERFACE vs ABSTRACT CLASS:
// ┌──────────────────────┬─────────────────────────┬──────────────────────────┐
// │                      │ Interface               │ Abstract Class           │
// ├──────────────────────┼─────────────────────────┼──────────────────────────┤
// │ Có code thực thi?    │ ❌ KHÔNG (chỉ khai báo) │ ✅ CÓ (có cả 2 loại)    │
// │ Số lượng implements? │ Nhiều interface         │ Chỉ 1 abstract class     │
// │ Từ khóa              │ implements              │ extends                  │
// │ Ví dụ trong dự án    │ MiddlewareInterface     │ BaseController           │
// └──────────────────────┴─────────────────────────┴──────────────────────────┘
//
// 🎯 TẠI SAO CẦN INTERFACE NÀY?
// Trong dự án có 3 middleware: JwtAuthMiddleware, OrderOwnerMiddleware, CorsMiddleware
// Cả 3 đều phải có method handle(). Interface này đảm bảo:
// - Nếu ai quên viết handle() → PHP sẽ báo lỗi ngay
// - Tất cả middleware đều tuân theo cùng 1 chuẩn
// ============================================================================

namespace App\Contracts;

use Closure;
use Illuminate\Http\Request;

/**
 * Interface MiddlewareInterface
 *
 * Hợp đồng (contract) buộc mọi middleware custom phải implement method handle().
 *
 * 📌 CÁC MIDDLEWARE SỬ DỤNG INTERFACE NÀY:
 *   1. JwtAuthMiddleware (TV5)   → Kiểm tra token JWT → 401 nếu sai
 *   2. OrderOwnerMiddleware (TV5) → Kiểm tra chủ đơn hàng → 403 nếu không phải
 *   3. CorsMiddleware (TV5)       → Thêm CORS headers cho cross-origin requests
 *
 * 📌 CÁCH SỬ DỤNG:
 *   class JwtAuthMiddleware implements MiddlewareInterface
 *   {
 *       public function handle(Request $request, Closure $next): mixed
 *       {
 *           // Code kiểm tra JWT token ở đây...
 *       }
 *   }
 */
interface MiddlewareInterface
{
    /**
     * Xử lý request HTTP đến.
     *
     * 📖 CÁCH MIDDLEWARE HOẠT ĐỘNG:
     * 1. Request đến → Middleware nhận request
     * 2. Middleware kiểm tra (VD: token có hợp lệ không?)
     * 3. Nếu OK → gọi $next($request) để chuyển tiếp cho controller
     * 4. Nếu KHÔNG OK → trả về response lỗi (401, 403...)
     *
     * 📌 VÍ DỤ THỰC TẾ:
     * Giống như bảo vệ ở cổng công ty:
     * - Bạn đưa thẻ nhân viên (token) → Bảo vệ kiểm tra
     * - Thẻ hợp lệ → Cho vào (gọi $next)
     * - Thẻ sai/hết hạn → Từ chối (trả về lỗi 401)
     *
     * @param  \Illuminate\Http\Request  $request  Request HTTP đến (chứa URL, headers, body...)
     * @param  \Closure  $next  Callback — gọi $next($request) để cho request đi tiếp
     * @return mixed  Response HTTP (JsonResponse lỗi hoặc tiếp tục pipeline)
     */
    public function handle(Request $request, Closure $next): mixed;
}
