<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    protected function getModel(): string { return Order::class; }

    public function index(Request $request) {
        // Admin xem tat ca, User chi xem cua minh
        $query = auth()->user()->isAdmin() ? Order::query() : Order::where('user_id', auth()->id());
        if ($request->has('status')) $query->where('status', $request->status);
        return ApiResponse::success(OrderResource::collection($query->latest()->paginate(10)));
    }

    public function show(string $id) {
        $order = Order::findOrFail($id);
        // Admin xem bat ky, User chi xem cua minh
        if (!auth()->user()->isAdmin() && $order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        return ApiResponse::success(new OrderResource($order));
    }

    public function store(Request $request) {
        $request->validate([
            'item_name'        => 'required|string|max:255',
            'quantity'         => 'required|integer|min:1',
            'total_price'      => 'required|numeric|min:1000',
            'shipping_address' => 'required|string|min:10',
            'payment_method'   => 'required|in:COD,Bank Transfer,Momo,ZaloPay,VNPay',
            'note'             => 'nullable|string|max:1000',
        ]);
        $order = Order::create(array_merge(
            $request->only(['item_name','quantity','total_price','shipping_address','payment_method','note']),
            ['user_id' => auth()->id(), 'status' => 'pending', 'order_number' => 'ORD-' . strtoupper(substr(uniqid(), -8))]
        ));
        return ApiResponse::created(new OrderResource($order), 'Tao don hang thanh cong');
    }

    public function update(Request $request, string $id) {
        $order = Order::findOrFail($id);
        if (!auth()->user()->isAdmin() && $order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi cap nhat khi status = pending', 422);
        $order->update($request->only(['item_name','quantity','total_price','shipping_address','payment_method','note']));
        return ApiResponse::success(new OrderResource($order->fresh()));
    }

    public function destroy(string $id) {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id()) return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi xoa khi status = pending', 422);
        $order->delete();
        return ApiResponse::success(null, 'Xoa don hang thanh cong');
    }

    public function myOrders(Request $request) {
        $q = Order::where('user_id', auth()->id());
        if ($request->has('status')) $q->where('status', $request->status);
        return ApiResponse::success(OrderResource::collection($q->latest()->paginate(10)));
    }

    public function confirmOrder(string $id) {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id()) return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi xac nhan khi status = pending', 422);
        $order->update(['status' => 'confirmed']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Da xac nhan');
    }

    public function cancelOrder(string $id) {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id()) return ApiResponse::forbidden('Khong co quyen');
        if (!in_array($order->status, ['pending','confirmed']))
            return ApiResponse::error('Khong the huy: ' . $order->status, 422);
        $order->update(['status' => 'cancelled']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Da huy');
    }

    // === ADMIN ONLY ===

    // Chuyen trang thai sang "dang giao hang"
    public function shipOrder(string $id) {
        $order = Order::findOrFail($id);
        if ($order->status !== 'confirmed')
            return ApiResponse::error('Chi chuyen giao khi da xac nhan', 422);
        $order->update(['status' => 'shipping']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Don hang dang duoc giao');
    }

    // Chuyen trang thai sang "da giao"
    public function deliverOrder(string $id) {
        $order = Order::findOrFail($id);
        if ($order->status !== 'shipping')
            return ApiResponse::error('Chi giao xong khi dang giao', 422);
        $order->update(['status' => 'delivered']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Don hang da giao thanh cong');
    }

    // Thong ke tong quan cho admin dashboard
    public function stats() {
        return ApiResponse::success([
            'total_orders'    => Order::count(),
            'pending'         => Order::where('status', 'pending')->count(),
            'confirmed'       => Order::where('status', 'confirmed')->count(),
            'shipping'        => Order::where('status', 'shipping')->count(),
            'delivered'        => Order::where('status', 'delivered')->count(),
            'cancelled'       => Order::where('status', 'cancelled')->count(),
            'total_users'     => \App\Models\User::count(),
            'total_revenue'   => Order::where('status', 'delivered')->sum('total_price'),
        ], 'Thong ke he thong');
    }
}