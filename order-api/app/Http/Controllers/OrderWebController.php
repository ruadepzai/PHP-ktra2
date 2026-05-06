<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderWebController extends Controller
{
    // Hàm hiển thị trang danh sách đơn
    public function index()
    {
        // Gọi đến file resources/views/orders/index.blade.php (của TV4)
        $orders = \App\Models\Order::paginate(10);
        return view('orders.index', compact('orders'));
    }
    public function create()
    {
        // Trả về giao diện (form) để người dùng điền thông tin đơn hàng mới
        return view('orders.create');
    }

    // Hàm hiển thị trang chi tiết 1 đơn
    public function show($id)
    {
        // Tìm đơn hàng trong Database dựa vào $id, nếu không thấy sẽ tự động báo lỗi 404
        $order = \App\Models\Order::findOrFail($id);

        // Trả về giao diện và "gửi kèm" biến $order sang cho HTML
        return view('orders.show', compact('order'));
    }
}