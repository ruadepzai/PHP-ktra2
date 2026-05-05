<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;
use App\Contracts\MiddlewareInterface;
use App\Http\Responses\ApiResponse;


class OrderOwnerMiddleware implements MiddlewareInterface
{
    /**
     * Xu ly request den
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lay order ID tu URL
        $orderId = $request->route('id');

        // Tim don hang trong database
        $order = Order::find($orderId);

        if (!$order) {
            return ApiResponse::error('Don hang khong ton tai', 404);
        }

        // So sanh user_id cua don hang voi user dang dang nhap
        if ($order->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Ban khong co quyen truy cap don hang nay');
        }
        // Gan order vao request de controller khong can query lai DB

        $request->merge(['order' => $order]);

        return $next($request);
    }
}
