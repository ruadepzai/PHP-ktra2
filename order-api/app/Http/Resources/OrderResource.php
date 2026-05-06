<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class OrderResource
 *
 * API Resource cho Order Model — đóng vai trò View layer trong kiến trúc MVC.
 * Chịu trách nhiệm format dữ liệu đơn hàng trước khi trả về cho client.
 *
 * Chức năng chính:
 * - Format các field cơ bản từ Order Model (id, total_amount, status...)
 * - Tính toán computed fields (status_label, can_cancel, can_update)
 * - Load relationship user (chỉ expose id, name, email — KHÔNG expose password, token)
 *
 * Cách sử dụng:
 * - Trả về 1 đơn hàng: new OrderResource($order)
 * - Trả về danh sách: OrderResource::collection($orders)
 *
 * @package App\Http\Resources
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Chuyển đổi Order Model thành mảng JSON với format chuẩn,
     * bao gồm các computed fields hỗ trợ client hiển thị giao diện.
     *
     * Computed fields:
     * - status_label: Tên trạng thái tiếng Việt (Chờ xử lý, Đã xác nhận...)
     * - can_cancel: Đơn hàng có thể hủy không (gọi isCancellable() từ Model)
     * - can_update: Đơn hàng có thể sửa không (chỉ khi status = pending)
     *
     * @param  \Illuminate\Http\Request  $request  Request HTTP hiện tại
     * @return array<string, mixed>  Mảng dữ liệu đơn hàng đã format
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'user_id'              => $this->user_id,
            'user'                 => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'order_number'         => $this->order_number,
            'total_amount'         => $this->total_amount,
            'status'               => $this->status,
            'status_label'         => $this->getStatusLabel(),
            'address'              => $this->address,
            'notes'                => $this->notes,
            'can_cancel'           => $this->isCancellable(),
            'can_update'           => $this->status === 'pending',
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }

    /**
     * Lấy label tiếng Việt cho trạng thái đơn hàng.
     *
     * Mapping:
     * - pending   → Chờ xử lý
     * - confirmed → Đã xác nhận
     * - shipping  → Đang giao hàng
     * - delivered → Đã giao
     * - cancelled → Đã hủy
     *
     * @return string  Label tiếng Việt của trạng thái
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending'   => 'Chờ xử lý',
            'confirmed' => 'Đã xác nhận',
            'shipping'  => 'Đang giao hàng',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            default     => 'Không xác định',
        };
    }
}
