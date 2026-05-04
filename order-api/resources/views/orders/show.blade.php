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
            {{-- Badge trang thai --}}
            @if($order->status === 'pending')
                <span class="badge bg-warning text-dark fs-6">Cho xu ly</span>
            @elseif($order->status === 'confirmed')
                <span class="badge bg-info text-dark fs-6">Da xac nhan</span>
            @elseif($order->status === 'shipping')
                <span class="badge bg-primary fs-6">Dang giao</span>
            @elseif($order->status === 'delivered')
                <span class="badge bg-success fs-6">Da giao</span>
            @elseif($order->status === 'cancelled')
                <span class="badge bg-danger fs-6">Da huy</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Ten hang:</div>
                <div class="col-sm-8">{{ $order->item_name }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">So luong:</div>
                <div class="col-sm-8">{{ $order->quantity }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Tong tien:</div>
                <div class="col-sm-8 fw-bold text-success">
                    {{ number_format($order->total_price, 0, ',', '.') }} VND
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Phuong thuc thanh toan:</div>
                <div class="col-sm-8">{{ $order->payment_method }}</div>
            </div>
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Dia chi giao hang:</div>
                <div class="col-sm-8">{{ $order->shipping_address }}</div>
            </div>

            {{-- Chi hien thi ghi chu neu co --}}
            @if($order->note)
            <div class="row mb-3">
                <div class="col-sm-4 fw-bold text-muted">Ghi chu:</div>
                <div class="col-sm-8 fst-italic">{{ $order->note }}</div>
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

        {{-- Nut Sua: chi hien khi pending --}}
        @if($order->status === 'pending')
        <a href="{{ route('orders.edit', $order->id) }}"
           class="btn btn-warning">
            Chinh sua don hang
        </a>
        @endif

        {{-- Nut Xac nhan: chi hien khi pending --}}
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

        {{-- Nut Huy: hien khi pending hoac confirmed --}}
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

        {{-- Nut Xoa: chi hien khi pending --}}
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
