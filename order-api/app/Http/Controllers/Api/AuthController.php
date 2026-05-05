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
     * DANG KY tai khoan moi
     * POST /api/auth/register
     * Thanh cong: 201 Created + token
     * That bai:  422 Validation Error
     */
    public function register(Request $request)
    {
        // Buoc 1: Validate du lieu dau vao
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            // 'confirmed' -> yeu cau field 'password_confirmation' phai khop
        ], [
            // Custom messages tieng Viet
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

        // Buoc 2: Tao user moi trong database
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            // Hash::make() ma hoa mat khau bang bcrypt
            // KHONG BAO GIO luu mat khau dang plaintext!
        ]);

        // Buoc 3: Tao JWT token tu user vua tao
        $token = JWTAuth::fromUser($user);

        // Buoc 4: Tra ve 201 Created + thong tin user + token
        return ApiResponse::success([
            'user'       => $user,
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60, // TTL tinh bang giay
        ], 'Dang ky thanh cong', 201);
    }

    /**
     * DANG NHAP
     * POST /api/auth/login
     * 
     * 
     * Thanh cong: 200 OK + token
     * That bai:  401 Unauthorized (sai email/password)
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

        // Buoc 2: Lay credentials (chi lay email va password)
        $credentials = $request->only('email', 'password');

        try {
            // Buoc 3: Thu dang nhap voi JWTAuth::attempt()
            // attempt() kiem tra email + password trong DB
            // Neu dung -> tra ve token
            // Neu sai -> tra ve false
            if (!$token = JWTAuth::attempt($credentials)) {
                return ApiResponse::error('Email hoac mat khau khong dung', 401);
            }
        } catch (JWTException $e) {
            // Loi he thong (VD: khong doc duoc JWT_SECRET)
            return ApiResponse::error('Khong the tao token', 500);
        }

        // Buoc 4: Dang nhap thanh cong -> tra ve token
        return ApiResponse::success([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
        ], 'Dang nhap thanh cong');
    }

    /**
     * DANG XUAT (huy token hien tai)
     * POST /api/auth/logout
     * 
     * Header: Authorization: Bearer {token}
     * 
     * Token se bi dua vao blacklist -> khong the su dung lai
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
     * LAY THONG TIN user dang dang nhap
     * GET /api/auth/me
     * 
     * Header: Authorization: Bearer {token}
     * 
     * Tra ve: thong tin user (name, email, created_at...)
     */
    public function me()
    {
        // auth()->user() tra ve User model cua nguoi dang dang nhap
        // (JwtAuthMiddleware da xac thuc truoc khi vao day)
        $user = auth()->user();

        return ApiResponse::success($user, 'Thong tin nguoi dung');
    }

    /**
     * LAM MOI TOKEN (gia han thoi gian su dung)
     * POST /api/auth/refresh
     * 
     * Header: Authorization: Bearer {token_cu}
     * 
     * Token cu se bi huy, tra ve token moi voi thoi gian moi
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
