{{-- resources/views/orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Danh sach don hang')

@section('content')
<div class="container mt-4">

    {{-- Tieu de trang + nut tao moi --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Danh sach don hang</h2>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            + Tao don hang moi
        </a>
    </div>

    {{-- Form filter theo status --}}
    <form method="GET" action="{{ route('orders.index') }}" class="mb-3">
        @csrf
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

    {{-- Bang danh sach don hang --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>STT</th>
                    <th>Ten hang</th>
                    <th>So luong</th>
                    <th>Tong tien</th>
                    <th>Phuong thuc TT</th>
                    <th>Trang thai</th>
                    <th>Ngay tao</th>
                    <th>Hanh dong</th>
                </tr>
            </thead>
            <tbody>
                {{-- @forelse: lap neu co data, @empty: hien thi neu khong co data --}}
                @forelse($orders as $order)
                <tr>
                    {{-- $loop->iteration: so thu tu bat dau tu 1 --}}
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->item_name }}</td>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} VND</td>
                    <td>{{ $order->payment_method }}</td>
                    <td>
                        {{-- Badge mau theo trang thai --}}
                        @if($order->status === 'pending')
                            <span class="badge bg-warning text-dark">Cho xu ly</span>
                        @elseif($order->status === 'confirmed')
                            <span class="badge bg-info text-dark">Da xac nhan</span>
                        @elseif($order->status === 'shipping')
                            <span class="badge bg-primary">Dang giao</span>
                        @elseif($order->status === 'delivered')
                            <span class="badge bg-success">Da giao</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge bg-danger">Da huy</span>
                        @endif
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        {{-- Nut xem chi tiet --}}
                        <a href="{{ route('orders.show', $order->id) }}"
                           class="btn btn-sm btn-outline-primary">Xem</a>

                        {{-- Nut xoa: chi hien khi pending --}}
                        @if($order->status === 'pending')
                        <form action="{{ route('orders.destroy', $order->id) }}"
                              method="POST" style="display:inline;"
                              onsubmit="return confirm('Xac nhan xoa don hang nay?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                Xoa
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                {{-- Hien thi khi khong co don hang nao --}}
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Khong co don hang nao.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phan trang: tu dong tao nut Prev / Next / 1 2 3 ... --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $orders->withQueryString()->links() }}
    </div>

</div>
@endsection
