<?php
// ============================================================================
// FILE: database/migrations/0001_01_01_000000_create_users_table.php
// LARAVEL MẶC ĐỊNH — Migration tạo bảng users
// ============================================================================
//
// 📖 MIGRATION LÀ GÌ?
// ---------------------
// Migration giống như "bản thiết kế" để tạo bảng trong database.
// Thay vì tự mở phpMyAdmin gõ SQL, bạn viết code PHP → Laravel tự tạo bảng.
//
//   Lệnh chạy: php artisan migrate
//   → Laravel đọc tất cả file trong database/migrations/ → tạo bảng theo thứ tự
//
// 📖 TẠI SAO CẦN BẢNG USERS?
// ----------------------------
// Bảng orders có cột: user_id (foreign key → trỏ tới bảng users)
// Nếu KHÔNG CÓ bảng users trước → tạo bảng orders sẽ LỖI:
//   "Cannot add foreign key constraint" (không tìm thấy bảng users để trỏ tới)
//
// 📖 TẠI SAO TÊN FILE BẮT ĐẦU BẰNG 0001_01_01?
// ------------------------------------------------
// Laravel chạy migration THEO THỨ TỰ TÊN FILE (alphabet/số).
// 0001_01_01_000000 < 2026_05_03_000000
// → Bảng users LUÔN được tạo TRƯỚC bảng orders (đúng thứ tự dependency)
// ============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;  // ← "Bản vẽ" cấu trúc bảng
use Illuminate\Support\Facades\Schema;      // ← Facade để tạo/xóa bảng

return new class extends Migration
{
    /**
     * Chạy migration — TẠO bảng users.
     *
     * 📖 TỪNG DÒNG NGHĨA LÀ GÌ:
     *   $table->id()                → Cột 'id' tự tăng (1, 2, 3...)
     *   $table->string('name')      → Cột 'name' kiểu VARCHAR(255) — tên user
     *   $table->string('email')     → Cột 'email' kiểu VARCHAR(255)
     *   ->unique()                  → Email KHÔNG ĐƯỢC trùng (mỗi user 1 email)
     *   $table->timestamp(...)      → Cột thời gian xác thực email (nullable = có thể trống)
     *   $table->string('password')  → Cột mật khẩu (đã hash, VD: $2y$12$xxx...)
     *   $table->rememberToken()     → Cột token "ghi nhớ đăng nhập" (nullable)
     *   $table->timestamps()        → Tự động tạo 2 cột: created_at, updated_at
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                        // ← Primary key, auto-increment
            $table->string('name');                              // ← Tên người dùng
            $table->string('email')->unique();                   // ← Email (không trùng)
            $table->timestamp('email_verified_at')->nullable();  // ← Thời gian xác thực email
            $table->string('password');                          // ← Mật khẩu (đã hash)
            $table->rememberToken();                             // ← Token "ghi nhớ đăng nhập"
            $table->timestamps();                                // ← created_at + updated_at
        });
    }

    /**
     * Rollback migration — XÓA bảng users.
     *
     * 📖 Khi chạy: php artisan migrate:rollback
     * → Laravel gọi down() để XÓA bảng, quay về trạng thái trước
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
