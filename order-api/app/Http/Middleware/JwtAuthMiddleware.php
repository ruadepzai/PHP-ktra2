<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Contracts\MiddlewareInterface;
use App\Http\Responses\ApiResponse;

/**
 * TV5 — JwtAuthMiddleware
 * 
 * Middleware xac thuc JWT Token (Authentication)
 * Kiem tra: "Ban la AI?" -> Tra 401 neu token khong hop le
 * 
 * Luong xu ly:
 *   1. Lay token tu header "Authorization: Bearer xxx"
 *   2. Giai ma token, xac thuc user
 *   3. Hop le -> cho di tiep ($next)
 *   4. Khong hop le -> tra 401 Unauthorized
 */
class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * Xu ly request den
     *
     * @param  Request  $request  — Thong tin request (URL, header, body...)
     * @param  Closure  $next     — Callback chuyen request sang middleware/controller tiep theo
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Buoc 1: Parse token tu header Authorization
            // Header phai co dang: "Authorization: Bearer eyJ..."
            $user = JWTAuth::parseToken()->authenticate();

            // Buoc 2: Token hop le nhung user khong ton tai trong DB
            // (VD: user da bi xoa nhung token chua het han)
            if (!$user) {
                return ApiResponse::error('Nguoi dung khong ton tai', 401);
            }

        } catch (TokenExpiredException $e) {
            // Token da het han (qua thoi gian TTL, mac dinh 60 phut)
            return ApiResponse::error('Token da het han, vui long dang nhap lai', 401);

        } catch (TokenInvalidException $e) {
            // Token bi sai format hoac bi chinh sua
            return ApiResponse::error('Token khong hop le', 401);

        } catch (JWTException $e) {
            // Khong tim thay token trong header
            // (Client quen gui header Authorization)
            return ApiResponse::error('Token khong duoc cung cap', 401);
        }

        // Buoc 3: Moi thu OK -> cho request di tiep toi middleware/controller ke tiep
        return $next($request);
    }
}