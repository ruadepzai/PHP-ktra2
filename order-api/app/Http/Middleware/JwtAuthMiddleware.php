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


class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * Xu ly request den
     * @param  Request  $request  — Thong tin request (URL, header, body...)
     * @param  Closure  $next     — Callback chuyen request sang middleware/controller tiep theo
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Parse token tu header Authorization
            $user = JWTAuth::parseToken()->authenticate();

            //Token hop le nhung user khong ton tai trong DB
            if (!$user) {
                return ApiResponse::error('Nguoi dung khong ton tai', 401);
            }

        } catch (TokenExpiredException $e) {
            // Token da het han
            return ApiResponse::error('Token da het han, vui long dang nhap lai', 401);

        } catch (TokenInvalidException $e) {
            // Token bi sai format hoac bi chinh sua
            return ApiResponse::error('Token khong hop le', 401);

        } catch (JWTException $e) {
            // Khong tim thay token trong header
            return ApiResponse::error('Token khong duoc cung cap', 401);
        }

        return $next($request);
    }
}
