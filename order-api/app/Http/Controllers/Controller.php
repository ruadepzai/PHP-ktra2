<?php
// ============================================================================
// FILE: app/Http/Controllers/Controller.php
// LARAVEL MẶC ĐỊNH — Base Controller
// ============================================================================
//
// 📖 FILE NÀY LÀ GÌ?
// --------------------
// Đây là class Controller GỐC mà Laravel tạo sẵn khi khởi tạo project.
// Nó là class CHA (parent class) mà TẤT CẢ controller trong project kế thừa:
//
//   Controller (file này)
//       ↑ extends
//   BaseController (TV2 — Abstract Class, chứa helper methods)
//       ↑ extends
//   OrderController (TV4 — xử lý CRUD đơn hàng)
//
//   Controller (file này)
//       ↑ extends
//   AuthController (TV5 — xử lý đăng nhập/đăng ký JWT)
//
// 📖 TẠI SAO FILE NÀY GẦN NHƯ TRỐNG?
// ------------------------------------
// Trong Laravel 11+, class Controller mặc định không chứa gì cả.
// Nhưng nó VẪN CẦN TỒN TẠI vì:
// - AuthController viết: class AuthController extends Controller ← cần file này
// - BaseController viết: abstract class BaseController extends Controller ← cần file này
// - Nếu KHÔNG CÓ file này → PHP báo: "Class Controller not found" → FATAL ERROR
//
// 📖 "abstract" NGHĨA LÀ GÌ?
// ----------------------------
// abstract class = class KHÔNG THỂ tạo object trực tiếp
// VD: $c = new Controller(); → ❌ LỖI (vì abstract)
// Chỉ dùng để cho class khác extends (kế thừa)
// ============================================================================

namespace App\Http\Controllers;

/**
 * Class Controller — Base controller mặc định của Laravel
 *
 * 📌 VAI TRÒ: Là class gốc trong chuỗi kế thừa Controller
 * 📌 AI EXTENDS: BaseController (TV2), AuthController (TV5)
 */
abstract class Controller
{
    // Class trống — chỉ đóng vai trò "class cha" để các controller khác extends
    // Trong Laravel phiên bản cũ, class này có use AuthorizesRequests, ValidatesRequests
    // Nhưng Laravel 11+ đã loại bỏ, chỉ giữ class trống
}
