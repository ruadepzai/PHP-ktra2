<?php
// ============================================================================
// FILE: app/Http/Requests/StoreOrderRequest.php
// TV2 — Form Request Validation (tạo đơn hàng)
// ============================================================================
//
// 📖 FORM REQUEST LÀ GÌ?
// ------------------------
// Form Request là cách Laravel tách logic VALIDATE DỮ LIỆU ra khỏi controller.
//
//   ❌ KHÔNG NÊN (validate trong controller → controller dài, khó bảo trì):
//       public function store(Request $request) {
//           $validator = Validator::make($request->all(), [
//               'total_amount' => 'required|numeric|min:1000',
//           ]);
//           if ($validator->fails()) return response()->json($validator->errors(), 422);
//           // ... code tạo đơn hàng
//       }
//
//   ✅ NÊN DÙNG Form Request (tách validation riêng):
//       public function store(StoreOrderRequest $request) {
//           // Laravel TỰ ĐỘNG validate TRƯỚC KHI vào controller
//           // Nếu validate thất bại → TỰ ĐỘNG trả lỗi 422 (không cần viết code)
//           $order = Order::create($request->validated());
//       }
//
// 🎯 CÁCH HOẠT ĐỘNG:
//   1. Client gửi POST /api/orders với body: { "total_amount": 500, "address": "abc" }
//   2. Laravel thấy store(StoreOrderRequest $request) → tự động validate
//   3. Nếu total_amount < 1000 → trả lỗi 422 + messages tiếng Việt
//   4. Nếu validate OK → mới chạy code trong store()
// ============================================================================

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;         // ← Class cha của Laravel
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class StoreOrderRequest — Validate dữ liệu TẠO đơn hàng mới
 *
 * 📌 FIELDS ĐƯỢC VALIDATE:
 *   - total_amount : Bắt buộc, là số, tối thiểu 1,000đ
 *   - address      : Bắt buộc, chuỗi, 10-500 ký tự
 *   - notes        : Tùy chọn, tối đa 1,000 ký tự
 *
 * 📌 FIELDS KHÔNG VALIDATE (tự động set trong controller):
 *   - status       : Luôn = 'pending' (controller set)
 *   - user_id      : Lấy từ auth()->id() (controller set)
 *   - order_number : Tự động generate (controller set)
 */
class StoreOrderRequest extends FormRequest
{
    /**
     * Xác định user có được phép gửi request này không.
     *
     * 📖 GIẢI THÍCH:
     * - return true  → Cho phép mọi user (vì JWT middleware đã xác thực rồi)
     * - return false → Từ chối (trả 403 Forbidden)
     *
     * Trong dự án, JwtAuthMiddleware (TV5) đã kiểm tra đăng nhập
     * TRƯỚC KHI request đến controller → nên ở đây luôn return true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validation.
     *
     * 📖 CÁCH ĐỌC RULES:
     *   'total_amount' => 'required|numeric|min:1000'
     *   Nghĩa là:
     *   - required : BẮT BUỘC phải có (không được bỏ trống)
     *   - numeric  : Phải là SỐ (1000, 50000.50, không phải "abc")
     *   - min:1000 : Giá trị TỐI THIỂU là 1000
     *
     *   'address' => 'required|string|min:10|max:500'
     *   Nghĩa là:
     *   - required : BẮT BUỘC
     *   - string   : Phải là CHUỖI KÝ TỰ
     *   - min:10   : Ít nhất 10 ký tự
     *   - max:500  : Nhiều nhất 500 ký tự
     *
     *   'notes' => 'nullable|string|max:1000'
     *   Nghĩa là:
     *   - nullable : CÓ THỂ TRỐNG (null) — không bắt buộc
     *   - string   : Nếu có thì phải là chuỗi
     *   - max:1000 : Tối đa 1,000 ký tự
     */
    public function rules(): array
    {
        return [
            'total_amount' => 'required|numeric|min:1000',
            'address'      => 'required|string|min:10|max:500',
            'notes'        => 'nullable|string|max:1000',
        ];
    }

    /**
     * Thông báo lỗi bằng tiếng Việt.
     *
     * 📖 CÁCH ĐỌC:
     *   'total_amount.required' → Khi total_amount vi phạm rule "required"
     *                             → Hiển thị message "Vui lòng nhập tổng tiền đơn hàng."
     *
     *   Nếu KHÔNG viết messages() → Laravel sẽ dùng tiếng Anh mặc định:
     *   "The total_amount field is required."
     */
    public function messages(): array
    {
        return [
            'total_amount.required' => 'Vui lòng nhập tổng tiền đơn hàng.',
            'total_amount.numeric'  => 'Tổng tiền phải là số.',
            'total_amount.min'      => 'Tổng tiền tối thiểu là 1,000đ.',
            'address.required'      => 'Vui lòng nhập địa chỉ giao hàng.',
            'address.string'        => 'Địa chỉ giao hàng phải là chuỗi ký tự.',
            'address.min'           => 'Địa chỉ giao hàng phải có ít nhất 10 ký tự.',
            'address.max'           => 'Địa chỉ giao hàng không được vượt quá 500 ký tự.',
            'notes.string'          => 'Ghi chú phải là chuỗi ký tự.',
            'notes.max'             => 'Ghi chú không được vượt quá 1,000 ký tự.',
        ];
    }

    /**
     * Xử lý khi validation THẤT BẠI — trả JSON thay vì redirect.
     *
     * 📖 TẠI SAO CẦN OVERRIDE METHOD NÀY?
     * Mặc định, Laravel FormRequest khi validation fail sẽ:
     *   - Web form: Redirect về trang trước + flash errors (cho Blade)
     *   - Nhưng API không có "trang trước" → cần trả JSON!
     *
     * Nên ta override (ghi đè) method failedValidation() để:
     *   - Throw HttpResponseException với JSON format chuẩn ApiResponse
     *   - Client nhận được lỗi dạng JSON, HTTP 422
     *
     * 📌 RESPONSE KHI VALIDATION THẤT BẠI:
     *   {
     *     "success": false,
     *     "message": "Dữ liệu không hợp lệ",
     *     "data": null,
     *     "errors": {
     *       "total_amount": ["Vui lòng nhập tổng tiền đơn hàng."],
     *       "address": ["Vui lòng nhập địa chỉ giao hàng."]
     *     },
     *     "code": 422
     *   }
     */
    protected function failedValidation(Validator $validator): void
    {
        // throw = "ném" exception → Laravel bắt và trả về response ngay
        // HttpResponseException = exception đặc biệt chứa sẵn response
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ',       // ← Message chung
                422,                             // ← HTTP 422 Unprocessable Entity
                $validator->errors()             // ← Chi tiết lỗi từng field
            )
        );
    }
}
