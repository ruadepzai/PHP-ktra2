<?php

namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

/**
 * Abstract Class BaseController
 *
 * Base controller cung cấp abstract CRUD methods và helper methods dùng chung.
 * Mọi API controller trong dự án phải extends class này.
 *
 * Design Pattern: Abstract Class + Template Method
 * - Abstract class KHÔNG thể khởi tạo trực tiếp (new BaseController() → ERROR)
 * - Định nghĩa 5 abstract methods (CRUD) → class con BẮT BUỘC phải implement
 * - Cung cấp 4 concrete helper methods → class con được kế thừa và sử dụng ngay
 *
 * So sánh với Interface:
 * - Interface chỉ có method signatures (hợp đồng), không có code thực thi
 * - Abstract Class có cả abstract methods (bắt buộc implement) lẫn concrete methods (code dùng chung)
 * - Một class chỉ extends được 1 Abstract Class nhưng implements được nhiều Interface
 *
 * Mở rộng trong tương lai:
 * - Nếu thêm CartController, ReviewController → chỉ cần extends BaseController
 * - Tự động có getCurrentUser(), authorizeOrderOwner(), successResponse(), errorResponse()
 * - Chỉ cần implement 5 abstract methods cho logic nghiệp vụ riêng
 *
 * Controllers sử dụng BaseController:
 * - OrderController (TV4): extends BaseController, implement 5 abstract methods
 *   + thêm myOrders(), confirmOrder(), cancelOrder()
 *
 * @package App\Http\Controllers
 */
abstract class BaseController extends Controller
{
    /**
     * Lấy danh sách tài nguyên.
     *
     * Class con implement method này để trả về danh sách resource
     * (ví dụ: danh sách đơn hàng, có phân trang và filter).
     *
     * @param  \Illuminate\Http\Request  $request  Request chứa query parameters (page, status, search...)
     * @return mixed  Response chứa danh sách resource
     */
    abstract public function index(Request $request);

    /**
     * Hiển thị chi tiết một tài nguyên.
     *
     * Class con implement method này để trả về thông tin chi tiết
     * của một resource theo ID.
     *
     * @param  string  $id  ID của resource cần xem
     * @return mixed  Response chứa chi tiết resource
     */
    abstract public function show(string $id);

    /**
     * Tạo mới một tài nguyên.
     *
     * Class con implement method này để xử lý logic tạo mới resource
     * (ví dụ: tạo đơn hàng mới với status 'pending').
     *
     * @param  \Illuminate\Http\Request  $request  Request chứa dữ liệu tạo mới
     * @return mixed  Response chứa resource vừa tạo
     */
    abstract public function store(Request $request);

    /**
     * Cập nhật một tài nguyên.
     *
     * Class con implement method này để xử lý logic cập nhật resource
     * (ví dụ: sửa địa chỉ giao hàng, ghi chú đơn hàng).
     *
     * @param  \Illuminate\Http\Request  $request  Request chứa dữ liệu cập nhật
     * @param  string  $id  ID của resource cần cập nhật
     * @return mixed  Response chứa resource đã cập nhật
     */
    abstract public function update(Request $request, string $id);

    /**
     * Xóa một tài nguyên.
     *
     * Class con implement method này để xử lý logic xóa resource
     * (ví dụ: xóa đơn hàng ở trạng thái pending).
     *
     * @param  string  $id  ID của resource cần xóa
     * @return mixed  Response xác nhận đã xóa
     */
    abstract public function destroy(string $id);

    // =====================================================================
    // CONCRETE HELPER METHODS — Dùng chung cho mọi Controller con
    // =====================================================================

    /**
     * Lấy thông tin user đang đăng nhập (đã xác thực JWT).
     *
     * Method này kiểm tra và trả về user hiện tại từ auth guard.
     * Nếu không có user (chưa đăng nhập, token hết hạn) → throw AuthenticationException.
     *
     * @return \App\Models\User  User đang đăng nhập
     *
     * @throws \Illuminate\Auth\AuthenticationException  Khi không có user đăng nhập
     *
     * @example
     * $user = $this->getCurrentUser();
     * $order->user_id = $user->id;
     */
    protected function getCurrentUser()
    {
        $user = auth()->user();

        if (!$user) {
            throw new AuthenticationException('Chưa xác thực. Vui lòng đăng nhập.');
        }

        return $user;
    }

    /**
     * Kiểm tra quyền sở hữu đơn hàng (backup cho middleware).
     *
     * So sánh user_id của đơn hàng với user đang đăng nhập.
     * Nếu không phải chủ sở hữu → trả về response 403 Forbidden.
     *
     * @param  \App\Models\Order  $order  Đơn hàng cần kiểm tra quyền
     * @return \Illuminate\Http\JsonResponse|null  Response 403 nếu không có quyền, null nếu hợp lệ
     *
     * @example
     * $unauthorized = $this->authorizeOrderOwner($order);
     * if ($unauthorized) return $unauthorized;
     */
    protected function authorizeOrderOwner(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Bạn không có quyền truy cập đơn hàng này');
        }

        return null;
    }

    /**
     * Shortcut tạo response thành công.
     *
     * Delegate sang ApiResponse::success() để đảm bảo format JSON nhất quán.
     *
     * @param  mixed   $data     Dữ liệu trả về
     * @param  string  $message  Thông báo thành công
     * @param  int     $code     HTTP status code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Thành công', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Shortcut tạo response lỗi.
     *
     * Delegate sang ApiResponse::error() để đảm bảo format JSON nhất quán.
     *
     * @param  string  $message  Thông báo lỗi
     * @param  int     $code     HTTP status code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'Có lỗi xảy ra', int $code = 400)
    {
        return ApiResponse::error($message, $code);
    }

    /**
     * Trả về tên Model class mà controller con quản lý.
     *
     * @return string  Tên class Model (VD: Order::class)
     */
    abstract protected function getModel(): string;
}
