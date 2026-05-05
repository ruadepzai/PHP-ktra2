<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Contracts\MiddlewareInterface;

/**
 * TV5 — CorsMiddleware
 * 
 * Middleware xu ly Cross-Origin Resource Sharing (CORS)
 * Cho phep frontend tu domain/port khac goi API
 * 
 * Vi du:
 *   Frontend: http://localhost:3000 (React/Vue)
 *   Backend:  http://localhost:8000 (Laravel)
 *   -> Khac port = Khac Origin -> Trinh duyet CHAN mac dinh
 *   -> CorsMiddleware them header cho phep
 * 
 * Luu y: Laravel 11 da co config/cors.php (TV3 da cau hinh),
 * nhung de bai yeu cau viet Middleware thu cong de the hien OOP.
 */
class CorsMiddleware implements MiddlewareInterface
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
        // Buoc 1: Kiem tra Preflight Request (OPTIONS)
        // Trinh duyet gui OPTIONS truoc khi gui request that (POST, PUT, DELETE)
        // De hoi server: "Toi co duoc phep gui request khong?"
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            // Buoc 2: Khong phai OPTIONS -> xu ly request binh thuong
            $response = $next($request);
        }

        // Buoc 3: Them CORS headers vao response
        $response->headers->set('Access-Control-Allow-Origin', '*');
        // '*' = cho phep moi domain. Trong production nen chi dinh cu the:
        // 'http://localhost:3000'

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        // Cac HTTP method duoc phep goi

        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept');
        // Cac header client duoc phep gui (Authorization de gui JWT token)

        $response->headers->set('Access-Control-Max-Age', '86400');
        // Cache preflight response trong 24h (86400 giay)
        // -> Trinh duyet khong can gui OPTIONS moi lan

        return $response;
    }
}