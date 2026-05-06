<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    protected function getModel(): string
    {
        return Order::class;
    }

    // Danh sach don hang
    public function index($request)
    {
        $query = Order::query();
        if ($request->has('status'))
            $query->where('status', $request->status);
        return ApiResponse::success(OrderResource::collection($query->latest()->paginate(10)));
    }

    // Chi tiet don hang
    public function show($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        return ApiResponse::success(new OrderResource($order));
    }

    // Tao don hang moi — dung StoreOrderRequest rules nhung qua Validator
    public function store($request)
    {
        $storeRequest = new StoreOrderRequest();
        $validator = Validator::make($request->all(), $storeRequest->rules(), $storeRequest->messages());
        if ($validator->fails()) return ApiResponse::validation($validator->errors());

        $order = Order::create(array_merge(
            $validator->validated(),
            ['user_id' => auth()->id(), 'status' => 'pending']
        ));
        return ApiResponse::created(new OrderResource($order), 'Tao don hang thanh cong');
    }

    // Cap nhat don hang (chi khi pending)
    public function update($request, $id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi cap nhat duoc khi status = pending', 422);
        $order->update($request->only([
            'item_name', 'quantity', 'total_price',
            'shipping_address', 'payment_method', 'note'
        ]));
        return ApiResponse::success(new OrderResource($order->fresh()));
    }

    // Xoa don hang (chi khi pending)
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi xoa duoc khi status = pending', 422);
        $order->delete();
        return ApiResponse::success(null, 'Xoa don hang thanh cong');
    }

    // Don hang cua toi
    public function myOrders(Request $request)
    {
        $q = Order::where('user_id', auth()->id());
        if ($request->has('status'))
            $q->where('status', $request->status);
        return ApiResponse::success(OrderResource::collection($q->latest()->paginate(10)));
    }

    // Xac nhan don hang
    public function confirmOrder($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        if ($order->status !== 'pending')
            return ApiResponse::error('Chi xac nhan duoc khi status = pending', 422);
        $order->update(['status' => 'confirmed']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Da xac nhan');
    }

    // Huy don hang
    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        if ($order->user_id !== auth()->id())
            return ApiResponse::forbidden('Khong co quyen');
        if (!in_array($order->status, ['pending', 'confirmed']))
            return ApiResponse::error('Khong the huy o trang thai: ' . $order->status, 422);
        $order->update(['status' => 'cancelled']);
        return ApiResponse::success(new OrderResource($order->fresh()), 'Da huy');
    }
}
