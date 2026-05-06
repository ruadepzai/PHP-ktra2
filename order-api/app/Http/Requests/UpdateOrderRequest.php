<?php

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Class UpdateOrderRequest
 *
 * Form Request Validation cho việc cập nhật đơn hàng.
 * Chỉ cho phép sửa 2 field: address và notes.
 *
 * KHÔNG cho phép sửa:
 * - total_amount: Tổng tiền không thể thay đổi sau khi tạo
 * - status: Trạng thái chỉ thay đổi qua API riêng (confirm, cancel)
 * - user_id: Chủ sở hữu đơn hàng không thể thay đổi
 * - order_number: Mã đơn hàng không thể thay đổi
 *
 * Sử dụng 'sometimes' validation rule:
 * - Chỉ validate field khi nó có mặt trong request body
 * - Cho phép partial update (PATCH behavior)
 *
 * @package App\Http\Requests
 */
class UpdateOrderRequest extends FormRequest
{
    /**
     * Xác định user có được phép thực hiện request này không.
     *
     * @return bool  Luôn trả về true
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validation cho việc cập nhật đơn hàng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address' => 'sometimes|required|string|min:10|max:500',
            'notes'   => 'nullable|string|max:1000',
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
            'address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'address.string'   => 'Địa chỉ giao hàng phải là chuỗi ký tự.',
            'address.min'      => 'Địa chỉ giao hàng phải có ít nhất 10 ký tự.',
            'address.max'      => 'Địa chỉ giao hàng không được vượt quá 500 ký tự.',
            'notes.string'     => 'Ghi chú phải là chuỗi ký tự.',
            'notes.max'        => 'Ghi chú không được vượt quá 1,000 ký tự.',
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
