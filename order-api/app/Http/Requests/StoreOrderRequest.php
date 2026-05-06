<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class StoreOrderRequest
 *
 * Form Request Validation cho việc tạo đơn hàng mới.
 * Sử dụng Laravel built-in Form Request pattern để tách logic validation
 * ra khỏi controller, giúp code sạch và dễ bảo trì.
 *
 * Các field được validate:
 * - total_amount: Bắt buộc, số, tối thiểu 1,000đ
 * - address: Bắt buộc, chuỗi, 10-500 ký tự
 * - notes: Tùy chọn, tối đa 1,000 ký tự
 *
 * KHÔNG validate:
 * - status: Tự động set 'pending' trong controller
 * - user_id: Lấy từ auth()->id() trong controller
 * - order_number: Tự động generate trong controller
 *
 * @package App\Http\Requests
 */
class StoreOrderRequest extends FormRequest
{
    /**
     * Xác định user có được phép thực hiện request này không.
     *
     * Luôn trả về true vì việc authorization đã được xử lý
     * ở tầng middleware JWT (JwtAuthMiddleware — TV5).
     *
     * @return bool  Luôn trả về true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validation cho việc tạo đơn hàng mới.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'total_amount' => 'required|numeric|min:1000',
            'address'      => 'required|string|min:10|max:500',
            'notes'        => 'nullable|string|max:1000',
        ];
    }

    /**
     * Thông báo lỗi validation bằng tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'total_amount.required' => 'Vui lòng nhập tổng tiền đơn hàng.',
            'total_amount.numeric'  => 'Tổng tiền phải là số.',
            'total_amount.min'      => 'Tổng tiền tối thiểu là 1,000đ.',
            'address.required'      => 'Vui lòng nhập địa chỉ giao hàng.',
            'address.string'        => 'Địa chỉ giao hàng phải là chuỗi ký tự.',
            'address.min'           => 'Địa chỉ giao hàng phải có ít nhất 10 ký tự.',
            'address.max'           => 'Địa chỉ giao hàng không được vượt quá 500 ký tự.',
            'notes.string'          => 'Ghi chú phải là chuỗi ký tự.',
            'notes.max'             => 'Ghi chú không được vượt quá 1,000 ký tự.',
        ];
    }

    /**
     * Xử lý khi validation thất bại — trả về JSON thay vì redirect.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                'Dữ liệu không hợp lệ',
                422,
                $validator->errors()
            )
        );
    }
}
