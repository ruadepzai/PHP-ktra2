<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

use App\Models\User;

class AdminController extends Controller
{
    /**
     * Lấy thống kê tổng quan cho Admin.
     * Trả về tổng số đơn hàng và tổng doanh thu.
     */
    public function stats()
    {
        if (auth()->user()->role !== 'admin') {
            return ApiResponse::forbidden('Bạn không có quyền truy cập trang quản trị');
        }

        $totalOrders = Order::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_price');
        
        $statusStats = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();

        return ApiResponse::success([
            'total_orders'  => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'status_stats'  => $statusStats
        ], 'Lấy thống kê thành công');
    }

    /**
     * Lấy danh sách thành viên
     */
    public function users()
    {
        if (auth()->user()->role !== 'admin') {
            return ApiResponse::forbidden('Bạn không có quyền truy cập trang quản trị');
        }

        $users = User::latest()->get();
        return ApiResponse::success($users, 'Lấy danh sách thành viên thành công');
    }

    /**
     * Chuyển trạng thái đơn hàng thành shipping
     */
    public function shipOrder($id)
    {
        if (auth()->user()->role !== 'admin') {
            return ApiResponse::forbidden('Bạn không có quyền truy cập trang quản trị');
        }

        $order = Order::findOrFail($id);
        if ($order->status !== 'confirmed') {
            return ApiResponse::error('Chỉ có thể giao đơn hàng đã được xác nhận (confirmed)', 422);
        }

        $order->update(['status' => 'shipping']);
        return ApiResponse::success($order->fresh(), 'Đã chuyển sang trạng thái đang giao hàng');
    }

    /**
     * Chuyển trạng thái đơn hàng thành delivered
     */
    public function deliverOrder($id)
    {
        if (auth()->user()->role !== 'admin') {
            return ApiResponse::forbidden('Bạn không có quyền truy cập trang quản trị');
        }

        $order = Order::findOrFail($id);
        if ($order->status !== 'shipping') {
            return ApiResponse::error('Chỉ có thể hoàn thành đơn hàng đang được giao (shipping)', 422);
        }

        $order->update(['status' => 'delivered']);
        return ApiResponse::success($order->fresh(), 'Đã chuyển sang trạng thái đã giao thành công');
    }
}
