<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Danh sách các trạng thái hợp lệ
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPING = 'shipping';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'address',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Mối quan hệ: Đơn hàng thuộc về một User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- LOCAL SCOPES (Nhiệm vụ 1.3) ---

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // --- BUSINESS LOGIC (Nhiệm vụ 1.4) ---

    /**
     * Kiểm tra đơn hàng có thể hủy không?
     * Quy tắc: Chỉ được hủy khi ở trạng thái PENDING hoặc CONFIRMED.
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Kiểm tra đơn hàng có thể sửa thông tin không?
     * Quy tắc: Chỉ được sửa khi đang PENDING.
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Kiểm tra logic chuyển đổi trạng thái (State Transition)
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowed = [
            self::STATUS_PENDING => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_SHIPPING, self::STATUS_CANCELLED],
            self::STATUS_SHIPPING => [self::STATUS_DELIVERED],
            self::STATUS_DELIVERED => [], // Trạng thái cuối
            self::STATUS_CANCELLED => [], // Trạng thái cuối
        ];

        return in_array($newStatus, $allowed[$this->status] ?? []);
    }
}
