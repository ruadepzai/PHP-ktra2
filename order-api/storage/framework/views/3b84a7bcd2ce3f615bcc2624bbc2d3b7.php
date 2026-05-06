<?php $__env->startSection('title', 'Danh sach don hang'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Danh sach don hang</h2>
    </div>

    <form method="GET" action="<?php echo e(route('orders.index')); ?>" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tat ca trang thai --</option>
                    <option value="pending"   <?php echo e(request('status')=='pending'   ? 'selected' : ''); ?>>Cho xu ly</option>
                    <option value="confirmed" <?php echo e(request('status')=='confirmed' ? 'selected' : ''); ?>>Da xac nhan</option>
                    <option value="shipping"  <?php echo e(request('status')=='shipping'  ? 'selected' : ''); ?>>Dang giao</option>
                    <option value="delivered" <?php echo e(request('status')=='delivered' ? 'selected' : ''); ?>>Da giao</option>
                    <option value="cancelled" <?php echo e(request('status')=='cancelled' ? 'selected' : ''); ?>>Da huy</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Loc</button>
                <a href="<?php echo e(route('orders.index')); ?>" class="btn btn-outline-danger">Xoa loc</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Ma don</th>
                    <th>Ten hang</th>
                    <th>SL</th>
                    <th>Tong tien</th>
                    <th>PT Thanh toan</th>
                    <th>Trang thai</th>
                    <th>Ngay tao</th>
                    <th>Hanh dong</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($loop->iteration); ?></td>
                    <td><?php echo e($order->order_number); ?></td>
                    <td><?php echo e($order->item_name); ?></td>
                    <td><?php echo e($order->quantity); ?></td>
                    <td><?php echo e(number_format($order->total_price, 0, ',', '.')); ?> VND</td>
                    <td><?php echo e($order->payment_method); ?></td>
                    <td>
                        <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $order->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($order->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                    </td>
                    <td><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
                    <td>
                        <a href="<?php echo e(route('orders.show', $order->id)); ?>"
                           class="btn btn-sm btn-outline-primary">Xem</a>

                        <?php if($order->status === 'pending'): ?>
                        <form action="<?php echo e(route('orders.destroy', $order->id)); ?>"
                              method="POST" style="display:inline;"
                              onsubmit="return confirm('Xac nhan xoa don hang nay?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Khong co don hang nao.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        <?php echo e($orders->withQueryString()->links()); ?>

    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\order-api\PHP-ktra2-main\order-api\resources\views/orders/index.blade.php ENDPATH**/ ?>