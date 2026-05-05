<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\MiddlewareInterface;


class CorsMiddleware implements MiddlewareInterface
{
    /**
     * Xu ly request den
     * @param  Request  $request
     * @param  Closure  $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiem tra Preflight Request (OPTIONS)

        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            // Khong phai OPTIONS -> xu ly request binh thuong
            $response = $next($request);
        }

        // Them CORS headers vao response
        $response->headers->set('Access-Control-Allow-Origin', '*');
        // '*' = cho phep moi domain. Trong production nen chi dinh cu the:

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        // Cac HTTP method duoc phep goi

        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        // Cac header client duoc phep gui (Authorization de gui JWT token)

        $response->headers->set('Access-Control-Max-Age', '86400');
        // Cache preflight response trong 24h
        // -> Trinh duyet khong can gui OPTIONS moi lan

        return $response;
    }
}
