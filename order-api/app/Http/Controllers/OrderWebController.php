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
    // 1. Hàm mở giao diện Sửa đơn hàng (edit)
    public function edit($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        return view('orders.edit', compact('order'));
    }

    // 2. Hàm Xóa đơn hàng (destroy)
    public function destroy($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->delete();
        // Xóa xong thì quay về trang danh sách
        return redirect()->route('orders.index')->with('success', 'Đã xóa đơn hàng thành công!');
    }

    // 3. Hàm Xác nhận đơn hàng (confirm)
    public function confirm($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->status = 'confirmed'; // Đổi trạng thái thành confirmed
        $order->save();
        // Lưu xong thì quay lại trang hiện tại
        return redirect()->back()->with('success', 'Đã xác nhận đơn hàng!');
    }

    // 4. Hàm Hủy đơn hàng (cancel)
    public function cancel($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $order->status = 'cancelled'; // Đổi trạng thái thành cancelled
        $order->save();
        return redirect()->back()->with('success', 'Đã hủy đơn hàng!');
    }
}