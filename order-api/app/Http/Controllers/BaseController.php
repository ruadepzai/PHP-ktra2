<?php
namespace App\Http\Controllers;

use App\Http\Responses\ApiResponse;
use App\Models\Order;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    abstract public function index(Request $request);
    abstract public function show(string $id);
    abstract public function store(Request $request);
    abstract public function update(Request $request, string $id);
    abstract public function destroy(string $id);
    abstract protected function getModel(): string;

    protected function getCurrentUser()
    {
        $user = auth()->user();
        if (!$user) {
            throw new AuthenticationException('Chua xac thuc.');
        }
        return $user;
    }

    protected function authorizeOrderOwner(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return ApiResponse::forbidden('Khong co quyen');
        }
        return null;
    }

    protected function successResponse($data = null, string $message = 'Thanh cong', int $code = 200)
    {
        return ApiResponse::success($data, $message, $code);
    }

    protected function errorResponse(string $message = 'Co loi', int $code = 400)
    {
        return ApiResponse::error($message, $code);
    }
}