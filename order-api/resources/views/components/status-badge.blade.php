{{--
    ============================================================================
    Blade Component: Status Badge
    ============================================================================
    
    Anonymous Blade Component hiển thị trạng thái đơn hàng dưới dạng badge (pill)
    với màu sắc, icon emoji và hiệu ứng animation tương ứng theo từng trạng thái.

    Pattern: Reusable Blade Component (tái sử dụng)
    - Đây là anonymous component → không cần class PHP riêng
    - Laravel tự động đăng ký dựa theo đường dẫn file trong resources/views/components/
    
    Cách sử dụng:
        <x-status-badge :status="$order->status" />
        <x-status-badge status="pending" />
    
    Các trạng thái hỗ trợ:
        - pending   : ⏳ Chờ xử lý    (nền vàng amber)
        - confirmed : ✅ Đã xác nhận   (nền xanh dương)
        - shipping  : 🚚 Đang giao     (nền tím)
        - delivered : ✅ Đã giao       (nền xanh lá)
        - cancelled : ❌ Đã hủy        (nền đỏ)
    ============================================================================
--}}

@props(['status'])

@php
    $config = match($status) {
        'pending'   => [
            'bg'     => '#FEF3C7',
            'text'   => '#92400E',
            'border' => '#FCD34D',
            'label'  => '⏳ Chờ xử lý',
        ],
        'confirmed' => [
            'bg'     => '#DBEAFE',
            'text'   => '#1E40AF',
            'border' => '#93C5FD',
            'label'  => '✅ Đã xác nhận',
        ],
        'shipping'  => [
            'bg'     => '#EDE9FE',
            'text'   => '#5B21B6',
            'border' => '#C4B5FD',
            'label'  => '🚚 Đang giao',
        ],
        'delivered' => [
            'bg'     => '#D1FAE5',
            'text'   => '#065F46',
            'border' => '#6EE7B7',
            'label'  => '✅ Đã giao',
        ],
        'cancelled' => [
            'bg'     => '#FEE2E2',
            'text'   => '#991B1B',
            'border' => '#FCA5A5',
            'label'  => '❌ Đã hủy',
        ],
        default     => [
            'bg'     => '#F3F4F6',
            'text'   => '#374151',
            'border' => '#D1D5DB',
            'label'  => 'Không xác định',
        ],
    };

    $animationClass = $status === 'shipping' ? 'status-badge--pulse' : '';
@endphp

<span
    {{ $attributes->merge([
        'class' => "status-badge {$animationClass}",
        'style' => implode(' ', [
            "background-color: {$config['bg']};",
            "color: {$config['text']};",
            "border: 1px solid {$config['border']};",
            "padding: 4px 14px;",
            "border-radius: 9999px;",
            "font-size: 0.75rem;",
            "font-weight: 600;",
            "display: inline-block;",
            "line-height: 1.5;",
            "white-space: nowrap;",
            "letter-spacing: 0.025em;",
            "transition: all 0.2s ease;",
        ]),
    ]) }}
>
    {{ $config['label'] }}
</span>
