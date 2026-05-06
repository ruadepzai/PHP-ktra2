<?php
// ============================================================================
// FILE: app/Http/Controllers/BaseController.php
// TV2 — Abstract Class + Template Method Pattern
// ============================================================================
//
// 📖 ABSTRACT CLASS LÀ GÌ?
// --------------------------
// Abstract class là class CHA mà:
// 1. KHÔNG THỂ tạo object trực tiếp: new BaseController() → ❌ LỖI
// 2. Chứa 2 loại method:
//    a) abstract method → CHỈ khai báo tên, KHÔNG có code → class con BẮT BUỘC viết code
//    b) concrete method → CÓ code hoàn chỉnh → class con ĐƯỢC dùng ngay (kế thừa)
//
// 📖 CÁCH HOẠT ĐỘNG TRONG DỰ ÁN:
//
//   BaseController (abstract - class CHA)
//   ├── abstract index()       ← Khai báo thôi, KHÔNG có code
//   ├── abstract show()        ← OrderController BẮT BUỘC phải viết code cho 5 method này
//   ├── abstract store()
//   ├── abstract update()
//   ├── abstract destroy()
//   ├── getCurrentUser()       ← CÓ code → OrderController dùng được ngay: $this->getCurrentUser()
//   ├── authorizeOrderOwner()  ← CÓ code → OrderController dùng được ngay
//   ├── successResponse()      ← CÓ code → OrderController dùng được ngay
//   └── errorResponse()        ← CÓ code → OrderController dùng được ngay
//        │
//        ▼
//   OrderController extends BaseController (class CON)
//   ├── index()        ← BẮT BUỘC viết code (vì abstract)
//   ├── show()         ← BẮT BUỘC viết code
//   ├── store()        ← BẮT BUỘC viết code
//   ├── update()       ← BẮT BUỘC viết code
//   ├── destroy()      ← BẮT BUỘC viết code
//   ├── myOrders()     ← Method riêng của OrderController
//   ├── confirmOrder() ← Method riêng
//   └── cancelOrder()  ← Method riêng
//
// 🎯 TẠI SAO CẦN ABSTRACT CLASS?
// Nếu sau này thêm CartController, ReviewController... chỉ cần:
//   class CartController extends BaseController { ... }
// → Tự động có getCurrentUser(), authorizeOrderOwner(), successResponse(), errorResponse()
// → Chỉ cần viết code cho 5 abstract methods (CRUD riêng cho Cart)
// ============================================================================

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;    // ← Import class ApiResponse (TV2) để gọi static methods
use App\Models\Order;                   // ← Import Order Model (TV1) để kiểm tra quyền sở hữu
use Illuminate\Auth\AuthenticationException;  // ← Exception khi chưa đăng nhập
use Illuminate\Http\Request;            // ← Class Request của Laravel — chứa dữ liệu HTTP request

/**
 * Abstract Class BaseController
 *
 * 📌 TỔNG QUAN:
 *   - 6 abstract methods (CRUD + getModel) → class con BẮT BUỘC implement
 *   - 4 concrete helper methods            → class con KẾ THỪA dùng ngay
 *
 * 📌 AI SỬ DỤNG CLASS NÀY?
 *   - OrderController (TV4): extends BaseController
 */

// "abstract" = không thể new BaseController(), chỉ có thể extends
// "extends Controller" = kế thừa từ Laravel Controller cơ bản (có sẵn)
abstract class BaseController extends Controller
{
    // =====================================================================
    // PHẦN 1: ABSTRACT METHODS — Class con BẮT BUỘC phải viết code
    // =====================================================================
    // Keyword "abstract" = chỉ khai báo, KHÔNG có thân hàm { }
    // Nếu OrderController quên viết 1 trong 5 method này → PHP báo lỗi FATAL ERROR

    /**
     * 📦 Lấy danh sách tài nguyên (VD: danh sách đơn hàng).
     *
     * Trong OrderController, method này sẽ:
     * - Query database lấy danh sách orders
     * - Hỗ trợ filter theo status (?status=pending)
     * - Phân trang (paginate)
     *
     * @param  Request  $request  Chứa query params: ?status=pending&page=2
     */
    abstract public function index($request);

    /**
     * 🔍 Xem chi tiết 1 tài nguyên (VD: chi tiết đơn hàng #5).
     *
     * Trong OrderController, method này sẽ:
     * - Tìm order theo $id
     * - Kiểm tra quyền sở hữu (đơn này có phải của mình không?)
     * - Trả về JSON chi tiết đơn hàng
     *
     * @param  string  $id  ID của resource — VD: "5" (lấy từ URL /api/orders/5)
     */
    abstract public function show($id);

    /**
     * ➕ Tạo mới tài nguyên (VD: tạo đơn hàng mới).
     *
     * Trong OrderController, method này sẽ:
     * - Validate dữ liệu (qua StoreOrderRequest)
     * - Tạo order mới với status = 'pending'
     * - Gán user_id = người đang đăng nhập
     * - Trả về 201 Created
     *
     * @param  Request  $request  Chứa body dữ liệu đơn hàng
     */
    abstract public function store($request);

    /**
     * ✏️ Cập nhật tài nguyên (VD: sửa địa chỉ đơn hàng).
     *
     * Trong OrderController, method này sẽ:
     * - Kiểm tra status === 'pending' (chỉ cho sửa khi đang chờ xử lý)
     * - Validate dữ liệu (qua UpdateOrderRequest)
     * - Cập nhật và trả về kết quả
     *
     * @param  Request  $request  Dữ liệu cần cập nhật
     * @param  string  $id       ID đơn hàng cần sửa
     */
    abstract public function update($request, $id);

    /**
     * 🗑️ Xóa tài nguyên (VD: xóa đơn hàng).
     *
     * Trong OrderController, method này sẽ:
     * - Kiểm tra status === 'pending' (chỉ cho xóa khi đang chờ xử lý)
     * - Xóa khỏi database
     * - Trả về thông báo "Xóa thành công"
     *
     * @param  string  $id  ID đơn hàng cần xóa
     */
    abstract public function destroy($id);

    /**
     * 🏷️ Trả về tên Model class mà controller quản lý.
     *
     * Trong OrderController: return Order::class;
     * Mục đích: Để BaseController biết đang làm việc với Model nào
     */
    abstract protected function getModel(): string;

    // =====================================================================
    // PHẦN 2: CONCRETE HELPER METHODS — Có code, class con dùng ngay
    // =====================================================================
    // Các method dưới đây đã có code hoàn chỉnh.
    // OrderController chỉ cần gọi: $this->getCurrentUser()
    // KHÔNG cần viết lại code (đó là sức mạnh của kế thừa!)

    /**
     * 👤 Lấy thông tin user đang đăng nhập.
     *
     * 📖 CÁCH HOẠT ĐỘNG:
     *   1. auth()->user() → Laravel tự động lấy user từ JWT token
     *   2. Nếu không có user (token sai/hết hạn) → ném lỗi AuthenticationException
     *   3. Nếu có user → trả về User Model
     *
     * 📌 CÁCH SỬ DỤNG TRONG ORDERCONTROLLER:
     *   $user = $this->getCurrentUser();  // ← Gọi bằng $this vì đã kế thừa
     *   $order->user_id = $user->id;      // ← Gán đơn hàng cho user hiện tại
     *
     * "protected" = chỉ class này và class con mới gọi được
     *               (bên ngoài KHÔNG gọi được)
     */
    protected function getCurrentUser()
    {
        // auth()->user() → Laravel helper, trả về User Model hoặc null
        $user = auth()->user();

        // Nếu $user là null → chưa đăng nhập hoặc token hết hạn
        if (!$user) {
            // throw = "ném" exception → Laravel sẽ tự bắt và trả về lỗi 401
            throw new AuthenticationException('Chưa xác thực. Vui lòng đăng nhập.');
        }

        return $user;  // ← Trả về User Model (có ->id, ->name, ->email...)
    }

    /**
     * 🔐 Kiểm tra quyền sở hữu đơn hàng.
     *
     * 📖 CÁCH HOẠT ĐỘNG:
     *   So sánh: $order->user_id (chủ đơn hàng) với auth()->id() (người đang đăng nhập)
     *   - Nếu KHÁC nhau → trả 403 Forbidden ("Không phải đơn của bạn!")
     *   - Nếu GIỐNG nhau → trả null ("OK, đây là đơn của bạn")
     *
     * 📌 CÁCH SỬ DỤNG TRONG ORDERCONTROLLER:
     *   $unauthorized = $this->authorizeOrderOwner($order);
     *   if ($unauthorized) return $unauthorized;  // ← Nếu có lỗi → trả về 403 ngay
     *   // Code tiếp tục ở đây... (chỉ chạy khi user là chủ đơn)
     *
     * ⚠️ ĐÂY LÀ LỚP BẢO VỆ THỨ 2 (defense in depth):
     *   - Lớp 1: OrderOwnerMiddleware (TV5) — kiểm tra ở tầng middleware
     *   - Lớp 2: Method này — kiểm tra lại ở tầng controller (backup)
     */
    protected function authorizeOrderOwner(Order $order)
    {
        // !== là so sánh nghiêm ngặt (strict): cả giá trị VÀ kiểu dữ liệu
        // VD: 1 !== "1" → true (khác kiểu: int vs string)
        if ($order->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Bạn không có quyền truy cập đơn hàng này');
        }

        return null;  // ← null = "Không có lỗi, user có quyền"
    }

    /**
     * ✅ Shortcut tạo response thành công.
     *
     * 📌 THAY VÌ VIẾT DÀI:
     *   return ApiResponse::success($data, 'Thành công', 200);
     *
     * 📌 CHỈ CẦN VIẾT NGẮN:
     *   return $this->successResponse($data);
     *
     * "Delegate" = method này KHÔNG tự xử lý, mà chuyển tiếp cho ApiResponse::success()
     */
    protected function successResponse($data = null, string $message = 'Thành công', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * ❌ Shortcut tạo response lỗi.
     *
     * 📌 CÁCH DÙNG:
     *   return $this->errorResponse('Không thể hủy đơn đang giao', 400);
     *   return $this->errorResponse('Đơn hàng không tồn tại', 404);
     */
    protected function errorResponse(string $message = 'Có lỗi xảy ra', int $code = 400)
    {
        return ApiResponse::error($message, $code);
    }
}
