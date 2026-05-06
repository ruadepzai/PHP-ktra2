{{-- resources/views/orders/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Chi tiet don hang #' . $order->id)

@section('content')
<div class="container mt-4" style="max-width: 760px;">

    {{-- Tieu de + nut quay lai --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Chi tiet don hang #{{ $order->id }}</h2>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
            &larr; Quay lai danh sach
        </a>
    </div>

    {{-- The thong tin chinh --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Thong tin don hang</strong>
            <x-status-badge :status="$order->status" />
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Ma don hang:</div>
                <div class="col-sm-8">{{ $order->order_number }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Tong tien:</div>
                <div class="col-sm-8 fw-bold text-success">
                    {{ number_format($order->total_amount, 0, ',', '.') }} VND
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Dia chi giao hang:</div>
                <div class="col-sm-8">{{ $order->address }}</div>
            </div>

            @if($order->notes)
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Ghi chu:</div>
                <div class="col-sm-8 fst-italic">{{ $order->notes }}</div>
            </div>
            @endif

            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Ngay tao:</div>
                <div class="col-sm-8">{{ $order->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            <div class="row">
                <div class="col-sm-4 fw-bold text-muted">Cap nhat lan cuoi:</div>
                <div class="col-sm-8">{{ $order->updated_at->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>
    </div>

    {{-- Khu vuc hanh dong --}}
    <div class="d-flex gap-2 flex-wrap">

        @if($order->status === 'pending')
        <a href="{{ route('orders.edit', $order->id) }}"
           class="btn btn-warning">
            Chinh sua don hang
        </a>
        @endif

        @if($order->status === 'pending')
        <form action="{{ route('orders.confirm', $order->id) }}" method="POST"
              onsubmit="return confirm('Xac nhan don hang nay?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success">
                Xac nhan don hang
            </button>
        </form>
        @endif

        @if(in_array($order->status, ['pending', 'confirmed']))
        <form action="{{ route('orders.cancel', $order->id) }}" method="POST"
              onsubmit="return confirm('Ban co chac chan muon huy don hang nay?')">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-danger">
                Huy don hang
            </button>
        </form>
        @endif

        @if($order->status === 'pending')
        <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
              onsubmit="return confirm('Xoa vinh vien don hang nay?')"
              class="ms-auto">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                Xoa don hang
            </button>
        </form>
        @endif

    </div>

</div>
@endsection
