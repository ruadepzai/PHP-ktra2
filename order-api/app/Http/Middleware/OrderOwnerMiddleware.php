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
    private function withCors($response)
    {
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        return $response;
    }

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
            return $this->withCors(ApiResponse::error('Don hang khong ton tai', 404));
        }

        // So sanh user_id cua don hang voi user dang dang nhap
        if ($order->user_id !== auth()->id()) {
            return $this->withCors(ApiResponse::forbidden('Ban khong co quyen truy cap don hang nay'));
        }
        // Gan order vao request de controller khong can query lai DB

        $request->merge(['order' => $order]);

        return $next($request);
    }
}
