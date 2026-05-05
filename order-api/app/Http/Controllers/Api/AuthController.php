<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Responses\ApiResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 *   1. Client gui email + password -> login() -> tra ve JWT token
 *   2. Client gui token trong header moi request sau do
 *   3. Client goi logout() -> token bi vo hieu hoa (blacklist)
 */
class AuthController extends Controller
{
    /**
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        // Validate du lieu dau vao
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'Ten la bat buoc',
            'email.required'     => 'Email la bat buoc',
            'email.email'        => 'Email khong dung dinh dang',
            'email.unique'       => 'Email da ton tai trong he thong',
            'password.required'  => 'Mat khau la bat buoc',
            'password.min'       => 'Mat khau phai co it nhat 6 ky tu',
            'password.confirmed' => 'Xac nhan mat khau khong khop',
        ]);

        // Neu validation that bai -> tra 422 voi danh sach loi
        if ($validator->fails()) {
            return ApiResponse::validation($validator->errors());
        }

        //Tao user moi trong database
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        //Tao JWT token tu user vua tao
        $token = JWTAuth::fromUser($user);

        //Tra ve 201 Created + thong tin user + token
        return ApiResponse::success([
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // TTL tinh bang giay
        ], 'Dang ky thanh cong', 201);
    }

    /**
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        // Buoc 1: Validate
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email la bat buoc',
            'email.email'       => 'Email khong dung dinh dang',
            'password.required' => 'Mat khau la bat buoc',
        ]);

        if ($validator->fails()) {
            return ApiResponse::validation($validator->errors());
        }

        //Lay credentials (chi lay email va password)
        $credentials = $request->only('email', 'password');

        try {
            //Thu dang nhap voi JWTAuth::attempt()
            if (!$token = JWTAuth::attempt($credentials)) {
                return ApiResponse::error('Email hoac mat khau khong dung', 401);
            }
        } catch (JWTException $e) {
            return ApiResponse::error('Khong the tao token', 500);
        }
        return ApiResponse::success([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Dang nhap thanh cong');
    }

    /**
     * POST /api/auth/logout
     */
    public function logout()
    {
        try {
            // Lay token hien tai va dua vao blacklist
            JWTAuth::invalidate(JWTAuth::getToken());

            return ApiResponse::success(null, 'Dang xuat thanh cong');
        } catch (JWTException $e) {
            return ApiResponse::error('Khong the dang xuat', 500);
        }
    }

    /**
     * GET /api/auth/me
     */
    public function me()
    {
        // auth()->user() tra ve User model cua nguoi dang dang nhap
        // (JwtAuthMiddleware da xac thuc truoc khi vao day)
        $user = auth()->user();

        return ApiResponse::success($user, 'Thong tin nguoi dung');
    }

    /**
     * POST /api/auth/refresh
     */
    public function refresh()
    {
        try {
            // Lay token cu, tao token moi
            // Token cu se tu dong bi blacklist
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return ApiResponse::success([
                'token'      => $newToken,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60,
            ], 'Token da duoc lam moi');
        } catch (JWTException $e) {
            return ApiResponse::error('Khong the lam moi token', 401);
        }
    }
}
