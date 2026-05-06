


<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>


<?php
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
?>


<span
    <?php echo e($attributes->merge([
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
    ])); ?>

>
    
    <?php echo e($config['label']); ?>

</span>
<?php /**PATH C:\xampp\htdocs\order-api\PHP-ktra2-main\order-api\resources\views/components/status-badge.blade.php ENDPATH**/ ?>