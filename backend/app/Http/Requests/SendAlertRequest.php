<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:customers,id',
            'order_id' => 'required|integer|exists:orders,id',
            'lot_number' => 'required|string|max:50',
            'message' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'The customer ID is required.',
            'customer_id.exists' => 'The selected customer does not exist.',
            'order_id.required' => 'The order ID is required.',
            'order_id.exists' => 'The selected order does not exist.',
            'lot_number.required' => 'The lot number is required.',
            'lot_number.max' => 'The lot number may not exceed 50 characters.',
            'message.max' => 'The message may not exceed 1000 characters.',
        ];
    }
}
