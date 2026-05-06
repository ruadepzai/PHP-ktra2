<?php
// ============================================================================
// FILE: app/Http/Resources/OrderResource.php
// TV2 — API Resource (View layer trong MVC)
// ============================================================================
//
// 📖 API RESOURCE LÀ GÌ?
// ------------------------
// API Resource là lớp "view" cho API — giống như Blade template cho web.
// Nó chịu trách nhiệm FORMAT dữ liệu trước khi trả về cho client.
//
//   Model (database)          →  Resource (format)        →  JSON (client nhận)
//   ┌──────────────────┐        ┌──────────────────┐        ┌──────────────────┐
//   │ id: 1            │   →    │ id: 1            │   →    │ {                │
//   │ user_id: 5       │        │ user_id: 5       │        │   "id": 1,       │
//   │ status: "pending"│        │ status: "pending"│        │   "status_label": │
//   │ password: "xxx"  │  ❌    │ status_label:    │        │   "Chờ xử lý",   │
//   │ (private data)   │        │   "Chờ xử lý"   │  ✅    │   "can_cancel":  │
//   └──────────────────┘        │ can_cancel: true │        │   true           │
//                               └──────────────────┘        │ }                │
//                                                           └──────────────────┘
//
// 🎯 TẠI SAO CẦN RESOURCE?
// 1. ẨN dữ liệu nhạy cảm (password, token, remember_token...)
// 2. THÊM computed fields (status_label, can_cancel, can_update)
// 3. FORMAT dữ liệu nhất quán cho mọi response
// ============================================================================

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;  // ← Class cha của Laravel

/**
 * Class OrderResource — Format dữ liệu đơn hàng cho API response
 *
 * 📌 CÁCH SỬ DỤNG:
 *   - 1 đơn hàng:     new OrderResource($order)
 *   - Nhiều đơn hàng:  OrderResource::collection($orders)
 *
 * 📌 COMPUTED FIELDS (tính toán thêm, không có trong database):
 *   - status_label → Tên tiếng Việt: "pending" → "Chờ xử lý"
 *   - can_cancel   → Đơn có thể hủy không? (true/false)
 *   - can_update   → Đơn có thể sửa không? (true/false)
 */
class OrderResource extends JsonResource
{
    /**
     * Chuyển đổi Order Model thành mảng JSON.
     *
     * 📖 METHOD NÀY LÀM GÌ?
     * Laravel sẽ tự động gọi toArray() khi bạn viết:
     *   return ApiResponse::success(new OrderResource($order));
     *
     * 📖 $this TRONG RESOURCE LÀ GÌ?
     * $this ở đây chính là Order Model được truyền vào.
     * VD: new OrderResource($order) → $this = $order
     * Nên $this->id = $order->id, $this->status = $order->status
     *
     * @param  Request  $request  Request HTTP hiện tại
     * @return array  Mảng dữ liệu đã format
     */
    public function toArray(Request $request): array
    {
        return [
            // --- CÁC FIELD CƠ BẢN (lấy trực tiếp từ database) ---
            'id'           => $this->id,            // ← ID đơn hàng (auto-increment)
            'user_id'      => $this->user_id,       // ← ID chủ đơn hàng
            'order_number' => $this->order_number,  // ← Mã đơn hàng (VD: ORD-ABC12345)
            'total_amount' => $this->total_amount,  // ← Tổng tiền (decimal)
            'status'       => $this->status,        // ← Trạng thái: pending/confirmed/shipping/delivered/cancelled
            'address'      => $this->address,       // ← Địa chỉ giao hàng
            'notes'        => $this->notes,         // ← Ghi chú đơn hàng (nullable)

            // --- RELATIONSHIP (mối quan hệ với bảng khác) ---
            // whenLoaded() = CHỈ hiển thị user khi đã eager load relationship
            // VD: Order::with('user')->find(1) → có user
            //     Order::find(1) → KHÔNG có user (tránh N+1 query)
            'user'         => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                    // ⚠️ KHÔNG expose: password, remember_token (bảo mật!)
                ];
            }),

            // --- COMPUTED FIELDS (tính toán, KHÔNG có trong database) ---

            // status_label: Chuyển "pending" → "Chờ xử lý" (tiếng Việt)
            // → Giúp frontend hiển thị tên trạng thái đẹp hơn
            'status_label' => $this->getStatusLabel(),

            // can_cancel: Đơn có thể hủy không?
            // → Gọi method isCancellable() từ Order Model (TV1)
            // → true khi status là "pending" hoặc "confirmed"
            'can_cancel'   => $this->isCancellable(),

            // can_update: Đơn có thể sửa không?
            // → Chỉ cho sửa khi đang "pending" (chờ xử lý)
            'can_update'   => $this->status === 'pending',

            // --- TIMESTAMPS (thời gian) ---
            'created_at'   => $this->created_at,    // ← Ngày tạo đơn
            'updated_at'   => $this->updated_at,    // ← Ngày cập nhật gần nhất
        ];
    }

    /**
     * Chuyển status tiếng Anh → tiếng Việt.
     *
     * 📖 MATCH EXPRESSION (PHP 8.0+):
     * Giống switch nhưng ngắn gọn hơn, và TRẢ VỀ giá trị luôn.
     *
     *   match($this->status) {
     *       'pending' => 'Chờ xử lý',    ← Nếu status = "pending", trả về "Chờ xử lý"
     *       'confirmed' => 'Đã xác nhận', ← Nếu status = "confirmed", trả về "Đã xác nhận"
     *       default => 'Không xác định',   ← Nếu không khớp cái nào
     *   }
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping'  => 'Đang giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            default     => 'Không xác định',  // ← Phòng trường hợp status lạ
        };
    }
}
