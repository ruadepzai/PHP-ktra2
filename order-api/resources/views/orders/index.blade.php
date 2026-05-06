{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Danh sach don hang')

@section('content')
<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Danh sach don hang</h2>
    </div>

    <form method="GET" action="{{ route('orders.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Tat ca trang thai --</option>
                    <option value="pending"   {{ request('status')=='pending'   ? 'selected' : '' }}>Cho xu ly</option>
                    <option value="confirmed" {{ request('status')=='confirmed' ? 'selected' : '' }}>Da xac nhan</option>
                    <option value="shipping"  {{ request('status')=='shipping'  ? 'selected' : '' }}>Dang giao</option>
                    <option value="delivered" {{ request('status')=='delivered' ? 'selected' : '' }}>Da giao</option>
                    <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Da huy</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary">Loc</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-danger">Xoa loc</a>
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
                @forelse($orders as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->item_name }}</td>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} VND</td>
                    <td>{{ $order->payment_method }}</td>
                    <td>
                        <x-status-badge :status="$order->status" />
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('orders.show', $order->id) }}"
                           class="btn btn-sm btn-outline-primary">Xem</a>

                        @if($order->status === 'pending')
                        <form action="{{ route('orders.destroy', $order->id) }}"
                              method="POST" style="display:inline;"
                              onsubmit="return confirm('Xac nhan xoa don hang nay?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">Xoa</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Khong co don hang nao.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $orders->withQueryString()->links() }}
    </div>

</div>
@endsection
