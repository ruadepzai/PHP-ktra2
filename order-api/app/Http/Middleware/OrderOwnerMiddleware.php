<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;
use App\Contracts\MiddlewareInterface;
use App\Http\Responses\ApiResponse;

/**
 * TV5 — OrderOwnerMiddleware
 * 
 * Middleware kiem tra quyen so huu don hang (Authorization)
 * Kiem tra: "Don hang nay co phai CUA BAN khong?" -> Tra 403 neu khong phai
 * 
 * Phan biet voi JwtAuthMiddleware:
 *   - JwtAuth:    "Ban la AI?"        -> 401 (Authentication)
 *   - OrderOwner: "Ban duoc LAM GI?"  -> 403 (Authorization)
 * 
 * Vi du: User A (id=1) co xem don hang cua User B (id=2) khong?
 *        -> KHONG -> 403 Forbidden
 */
class OrderOwnerMiddleware implements MiddlewareInterface
{
    /**
     * Xu ly request den
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Buoc 1: Lay order ID tu URL
        // VD: GET /api/orders/5 -> $orderId = 5
        $orderId = $request->route('id');

        // Buoc 2: Tim don hang trong database
        $order = Order::find($orderId);

        // Buoc 3: Don hang khong ton tai -> 404
        if (!$order) {
            return ApiResponse::error('Don hang khong ton tai', 404);
        }

        // Buoc 4: So sanh user_id cua don hang voi user dang dang nhap
        // auth()->id() tra ve ID cua user hien tai (da duoc JwtAuthMiddleware xac thuc)
        if ($order->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Ban khong co quyen truy cap don hang nay');
        }

        // Buoc 5: Gan order vao request de controller khong can query lai DB
        // Trong controller: $order = $request->get('order');
        $request->merge(['order' => $order]);

        // Buoc 6: Moi thu OK -> cho di tiep
        return $next($request);
    }
}