<?php
// ============================================================================
// FILE: app/Http/Requests/UpdateOrderRequest.php
// TV2 — Form Request Validation (cập nhật đơn hàng)
// ============================================================================

namespace App\Http\Requests;

use App\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item_name'        => 'sometimes|required|string|max:255',
            'quantity'         => 'sometimes|required|integer|min:1|max:999',
            'total_price'      => 'sometimes|required|numeric|min:1000',
            'shipping_address' => 'sometimes|required|string|min:10|max:500',
            'payment_method'   => 'sometimes|required|string|in:COD,Bank Transfer,Momo,ZaloPay,VNPay',
            'note'             => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'item_name.required'        => 'Vui lòng nhập tên sản phẩm.',
            'item_name.string'          => 'Tên sản phẩm phải là chuỗi ký tự.',
            'item_name.max'             => 'Tên sản phẩm không được vượt quá 255 ký tự.',
            'quantity.required'         => 'Vui lòng nhập số lượng.',
            'quantity.integer'          => 'Số lượng phải là số nguyên.',
            'quantity.min'              => 'Số lượng tối thiểu là 1.',
            'quantity.max'              => 'Số lượng tối đa là 999.',
            'total_price.required'      => 'Vui lòng nhập tổng tiền đơn hàng.',
            'total_price.numeric'       => 'Tổng tiền phải là số.',
            'total_price.min'           => 'Tổng tiền tối thiểu là 1,000đ.',
            'shipping_address.required' => 'Vui lòng nhập địa chỉ giao hàng.',
            'shipping_address.string'   => 'Địa chỉ giao hàng phải là chuỗi ký tự.',
            'shipping_address.min'      => 'Địa chỉ giao hàng phải có ít nhất 10 ký tự.',
            'shipping_address.max'      => 'Địa chỉ giao hàng không được vượt quá 500 ký tự.',
            'payment_method.required'   => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'         => 'Phương thức thanh toán không hợp lệ.',
            'note.string'              => 'Ghi chú phải là chuỗi ký tự.',
            'note.max'                 => 'Ghi chú không được vượt quá 1,000 ký tự.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error('Dữ liệu không hợp lệ', 422, $validator->errors())
        );
    }
}
