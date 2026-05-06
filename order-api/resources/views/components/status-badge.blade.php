{{--
    ============================================================================
    FILE: resources/views/components/status-badge.blade.php
    TV2 — Blade Component (Anonymous Component)
    ============================================================================

    📖 BLADE COMPONENT LÀ GÌ?
    ────────────────────────────
    Component là một phần giao diện có thể TÁI SỬ DỤNG.
    Giống như bạn tạo 1 "khuôn mẫu" → dùng đi dùng lại ở nhiều nơi.

    VD: Badge trạng thái đơn hàng xuất hiện ở:
      - Trang danh sách đơn hàng (index.blade.php)
      - Trang chi tiết đơn hàng (show.blade.php)
      → Thay vì copy-paste code badge, ta tạo 1 component dùng chung.

    📖 ANONYMOUS COMPONENT LÀ GÌ?
    ────────────────────────────────
    - "Anonymous" = Không cần tạo class PHP riêng
    - Laravel tự đăng ký dựa vào vị trí file:
      resources/views/components/status-badge.blade.php
      → Tự động có tag: <x-status-badge />

    📌 CÁCH SỬ DỤNG:
      <x-status-badge :status="$order->status" />

    📌 GIẢI THÍCH CÚ PHÁP:
      - <x-status-badge>  → Gọi component (x- là prefix bắt buộc)
      - :status="..."     → Dấu : (colon) = truyền biến PHP (dynamic)
      - status="pending"  → Không có dấu : = truyền chuỗi tĩnh (static)

    📖 @props LÀ GÌ?
    ──────────────────
    @props(['status']) → Khai báo biến mà component NHẬN TỪ BÊN NGOÀI
    Giống parameter của function:
      function statusBadge($status) { ... }
    
    ============================================================================
--}}

{{-- 
    @props = Khai báo biến nhận từ bên ngoài
    Khi viết: <x-status-badge :status="$order->status" />
    → $status sẽ = giá trị của $order->status (VD: "pending")
--}}
@props(['status'])

{{-- 
    @php / @endphp = Viết code PHP trong Blade template
    
    📖 MATCH EXPRESSION:
    match() giống switch nhưng ngắn gọn hơn, trả về giá trị luôn.
    
    match($status) {
        'pending' => [...],     ← Nếu $status = "pending", trả về mảng cấu hình
        'confirmed' => [...],   ← Nếu $status = "confirmed", trả về mảng khác
        default => [...]        ← Nếu không khớp cái nào
    }
    
    Mỗi trạng thái có 4 thuộc tính:
    - bg     : Màu nền (background)
    - text   : Màu chữ
    - border : Màu viền
    - label  : Text hiển thị (có emoji)
--}}
@php
    $config = match($status) {
        'pending'   => [
            'bg'     => '#FEF3C7',     // Vàng nhạt — "đang chờ"
            'text'   => '#92400E',     // Nâu đậm — dễ đọc trên nền vàng
            'border' => '#FCD34D',     // Viền vàng
            'label'  => '⏳ Chờ xử lý',
        ],
        'confirmed' => [
            'bg'     => '#DBEAFE',     // Xanh dương nhạt — "đã xác nhận"
            'text'   => '#1E40AF',     // Xanh dương đậm
            'border' => '#93C5FD',
            'label'  => '✅ Đã xác nhận',
        ],
        'shipping'  => [
            'bg'     => '#EDE9FE',     // Tím nhạt — "đang giao"
            'text'   => '#5B21B6',     // Tím đậm
            'border' => '#C4B5FD',
            'label'  => '🚚 Đang giao',
        ],
        'delivered' => [
            'bg'     => '#D1FAE5',     // Xanh lá nhạt — "hoàn tất"
            'text'   => '#065F46',     // Xanh lá đậm
            'border' => '#6EE7B7',
            'label'  => '✅ Đã giao',
        ],
        'cancelled' => [
            'bg'     => '#FEE2E2',     // Đỏ nhạt — "đã hủy"
            'text'   => '#991B1B',     // Đỏ đậm
            'border' => '#FCA5A5',
            'label'  => '❌ Đã hủy',
        ],
        default     => [
            'bg'     => '#F3F4F6',     // Xám nhạt — "không xác định"
            'text'   => '#374151',
            'border' => '#D1D5DB',
            'label'  => 'Không xác định',
        ],
    };

    // Nếu đang giao hàng → thêm animation nhấp nháy (pulse)
    // → Giúp người dùng chú ý đơn đang vận chuyển
    $animationClass = $status === 'shipping' ? 'status-badge--pulse' : '';
@endphp

{{--
    📖 $attributes->merge() LÀ GÌ?
    ─────────────────────────────────
    Cho phép component nhận thêm attributes tùy ý từ bên ngoài.
    
    VD: <x-status-badge :status="$order->status" class="ms-2" id="badge-1" />
    → class "ms-2" và id "badge-1" sẽ được MERGE vào tag <span>
    
    Đây là tính năng mạnh của Blade Component — linh hoạt như HTML element thật.
--}}
<span
    {{ $attributes->merge([
        'class' => "status-badge {$animationClass}",
        'style' => implode(' ', [
            "background-color: {$config['bg']};",
            "color: {$config['text']};",
            "border: 1px solid {$config['border']};",
            "padding: 4px 14px;",
            "border-radius: 9999px;",          {{-- Viền tròn hoàn toàn (pill shape) --}}
            "font-size: 0.75rem;",             {{-- Cỡ chữ nhỏ --}}
            "font-weight: 600;",               {{-- Chữ đậm vừa --}}
            "display: inline-block;",          {{-- Hiển thị inline --}}
            "line-height: 1.5;",
            "white-space: nowrap;",            {{-- Không xuống dòng --}}
            "letter-spacing: 0.025em;",        {{-- Giãn chữ nhẹ --}}
            "transition: all 0.2s ease;",      {{-- Hiệu ứng mượt khi hover --}}
        ]),
    ]) }}
>
    {{-- Hiển thị label (VD: "⏳ Chờ xử lý", "✅ Đã xác nhận"...) --}}
    {{ $config['label'] }}
</span>
