<?php
// ============================================================================
// FILE: public/index.php
// LARAVEL MẶC ĐỊNH — Front Controller (Điểm vào duy nhất)
// ============================================================================
//
// 📖 FRONT CONTROLLER PATTERN LÀ GÌ?
// ------------------------------------
// Trong kiến trúc MVC, mọi request HTTP đều đi qua 1 FILE DUY NHẤT.
// File đó chính là public/index.php — gọi là "Front Controller".
//
// 📖 LUỒNG XỬ LÝ:
//
//   Trình duyệt / Postman
//        │
//        ▼
//   http://localhost/order-api/public/
//        │
//        ▼
//   ┌──────────────────────────────────┐
//   │  public/index.php  (FILE NÀY)   │  ← MỌI request đều vào đây
//   │  1. Load autoload.php (Composer) │
//   │  2. Load bootstrap/app.php       │
//   │  3. Tạo Laravel Application      │
//   │  4. Xử lý request               │
//   └──────────────────────────────────┘
//        │
//        ▼
//   ┌──────────────────────────────────┐
//   │  Middleware Pipeline             │
//   │  JwtAuthMiddleware → kiểm tra   │
//   │  OrderOwnerMiddleware → phân    │
//   │  quyền                          │
//   └──────────────────────────────────┘
//        │
//        ▼
//   ┌──────────────────────────────────┐
//   │  Router (api.php / web.php)     │
//   │  Match URL → Controller method   │
//   └──────────────────────────────────┘
//        │
//        ▼
//   ┌──────────────────────────────────┐
//   │  Controller xử lý logic         │
//   │  → Trả về JSON hoặc HTML        │
//   └──────────────────────────────────┘
//
// 📖 TẠI SAO CHỈ CÓ 1 ĐIỂM VÀO?
// --------------------------------
// - Bảo mật: Chỉ thư mục public/ được expose ra internet
//   (các file .env, config, database... nằm NGOÀI public/ → không ai truy cập được)
// - Tập trung: Mọi request đều qua middleware → kiểm tra authentication, logging, CORS...
// - Đây là pattern mà tất cả framework hiện đại đều dùng (Laravel, Django, Express...)
//
// ⚠️ QUAN TRỌNG CHO KIỂM TRA:
// Đây là 1 trong 4 OOP Patterns cần giải thích: Front Controller Pattern
// Câu hỏi thường gặp: "Tại sao mọi URL đều đi qua index.php?"
// → Trả lời: Để tập trung xử lý, áp dụng middleware, routing, và bảo mật
// ============================================================================

use Illuminate\Http\Request;

// ──────────────────────────────────────────────────────────
// BƯỚC 1: Xác định thời điểm ứng dụng bắt đầu chạy
// ──────────────────────────────────────────────────────────
define('LARAVEL_START', microtime(true));
// microtime(true) = thời gian hiện tại tính bằng giây (VD: 1699123456.789)
// Dùng để đo performance: "Ứng dụng xử lý request trong bao lâu?"

// ──────────────────────────────────────────────────────────
// BƯỚC 2: Kiểm tra chế độ bảo trì (maintenance mode)
// ──────────────────────────────────────────────────────────
// Khi chạy: php artisan down → Laravel tạo file này
// → Mọi request sẽ nhận response "503 Service Unavailable"
// → Hữu ích khi đang deploy/update code
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// ──────────────────────────────────────────────────────────
// BƯỚC 3: Load Composer Autoloader
// ──────────────────────────────────────────────────────────
// autoload.php được Composer tạo ra (sau khi chạy composer install)
// Nó giúp PHP tự động tìm và load class khi cần, không cần require thủ công
//
// VD: Khi code viết "use App\Models\Order;" 
//     → Autoloader tự tìm file app/Models/Order.php → load vào
require __DIR__.'/../vendor/autoload.php';

// ──────────────────────────────────────────────────────────
// BƯỚC 4: Khởi tạo Laravel Application
// ──────────────────────────────────────────────────────────
// bootstrap/app.php (TV3) chứa cấu hình:
// - Routes (api.php, web.php)
// - Middleware aliases (jwt.auth, order.owner, cors)
// - Exception handling (Handler.php)
$app = require_once __DIR__.'/../bootstrap/app.php';

// ──────────────────────────────────────────────────────────
// BƯỚC 5: Xử lý HTTP Request và trả về Response
// ──────────────────────────────────────────────────────────
// handleRequest() là method "ma thuật" của Laravel:
// 1. Nhận request từ trình duyệt/Postman
// 2. Chạy qua middleware pipeline (JWT, CORS, Owner...)
// 3. Match route (api.php hoặc web.php)
// 4. Gọi controller method (OrderController@index, AuthController@login...)
// 5. Nhận response từ controller
// 6. Gửi response về cho client
$app->handleRequest(Request::capture());
