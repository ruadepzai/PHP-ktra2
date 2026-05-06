<?php
// ============================================================================
// FILE: app/Http/Requests/UpdateOrderRequest.php
// TV2 — Form Request Validation (cập nhật đơn hàng)
// ============================================================================
//
// 📖 FILE NÀY KHÁC GÌ SO VỚI StoreOrderRequest?
// ────────────────────────────────────────────────
// StoreOrderRequest (TẠO MỚI):
//   - total_amount: BẮT BUỘC (required) — phải nhập khi tạo
//   - address:      BẮT BUỘC (required)
//   - notes:        Tùy chọn (nullable)
//
// UpdateOrderRequest (CẬP NHẬT):
//   - total_amount: ❌ KHÔNG CHO SỬA (không có trong rules)
//   - address:      sometimes (chỉ validate NẾU client gửi lên)
//   - notes:        Tùy chọn (nullable)
//
// 📖 "SOMETIMES" RULE LÀ GÌ?
// ─────────────────────────────
//   'address' => 'sometimes|required|string|min:10|max:500'
//
//   - "sometimes" = CHỈ validate khi field có trong request body
//   - Nếu client gửi: { "notes": "ghi chú mới" } (không có address)
//     → address KHÔNG bị validate → chỉ cập nhật notes
//   - Nếu client gửi: { "address": "abc" }
//     → address BỊ validate → "abc" chỉ 3 ký tự < 10 → lỗi!
//
//   Đây gọi là PARTIAL UPDATE (cập nhật 1 phần), phù hợp với HTTP PATCH
// ============================================================================

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class UpdateOrderRequest — Validate dữ liệu CẬP NHẬT đơn hàng
 *
 * 📌 FIELDS CHO PHÉP SỬA:
 *   - address : Địa chỉ giao hàng (sometimes — không bắt buộc gửi)
 *   - notes   : Ghi chú (nullable — có thể set null để xóa ghi chú)
 *
 * 📌 FIELDS KHÔNG CHO PHÉP SỬA:
 *   - total_amount  : Tổng tiền không thể thay đổi sau khi tạo
 *   - order_number  : Mã đơn không thể thay đổi
 *   - status        : Trạng thái chỉ thay đổi qua confirm/cancel API
 *   - user_id       : Chủ sở hữu không thể thay đổi
 */
class UpdateOrderRequest extends FormRequest
{
    /**
     * Luôn cho phép — JWT middleware đã xác thực rồi.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validation cho cập nhật.
     *
     * 📖 SO SÁNH VỚI StoreOrderRequest:
     *   Store:  'address' => 'required|string|min:10|max:500'         ← BẮT BUỘC
     *   Update: 'address' => 'sometimes|required|string|min:10|max:500' ← CHỈ khi có trong body
     *
     * 📖 CHÚ Ý: "sometimes|required" không mâu thuẫn:
     *   - "sometimes" = "chỉ kiểm tra khi field tồn tại"
     *   - "required"  = "nếu tồn tại thì KHÔNG ĐƯỢC TRỐNG"
     *   Kết hợp: "Nếu gửi address thì phải có giá trị, không được gửi rỗng"
     */
    public function rules(): array
    {
        return [
            'address' => 'sometimes|required|string|min:10|max:500',
            'notes'   => 'nullable|string|max:1000',
        ];
    }

    /**
     * Thông báo lỗi bằng tiếng Việt.
     */
    public function messages(): array
    {
        return [
            'address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'address.string'   => 'Địa chỉ giao hàng phải là chuỗi ký tự.',
            'address.min'      => 'Địa chỉ giao hàng phải có ít nhất 10 ký tự.',
            'address.max'      => 'Địa chỉ giao hàng không được vượt quá 500 ký tự.',
            'notes.string'     => 'Ghi chú phải là chuỗi ký tự.',
            'notes.max'        => 'Ghi chú không được vượt quá 1,000 ký tự.',
        ];
    }

    /**
     * Override: Trả JSON 422 thay vì redirect (giống StoreOrderRequest).
     *
     * 📖 Xem giải thích chi tiết ở StoreOrderRequest.php
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ',
                422,
                $validator->errors()
            )
        );
    }
}
