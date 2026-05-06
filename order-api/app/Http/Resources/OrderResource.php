<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'user'             => $this->whenLoaded('user', function () {
                return [
                    'id'    => $this->user->id,
                    'name'  => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'order_number'     => $this->order_number,
            'item_name'        => $this->item_name,
            'quantity'         => $this->quantity,
            'total_price'      => $this->total_price,
            'status'           => $this->status,
            'status_label'     => $this->getStatusLabel(),
            'shipping_address' => $this->shipping_address,
            'payment_method'   => $this->payment_method,
            'note'             => $this->note,
            'can_cancel'       => $this->isCancellable(),
            'can_update'       => $this->status === 'pending',
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }

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
