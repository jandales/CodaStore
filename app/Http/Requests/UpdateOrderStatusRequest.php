<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    /** Allowed next statuses for each current status. */
    private const TRANSITIONS = [
        Order::STATUS_CONFIRMED => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
        Order::STATUS_SHIPPED => [Order::STATUS_COMPLETED],
    ];

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $order = $this->route('order');
        $allowed = self::TRANSITIONS[$order->status ?? ''] ?? [];

        return [
            'status' => ['required', Rule::in($allowed)],
            'courier' => ['required_if:status,' . Order::STATUS_SHIPPED, 'nullable', Rule::in(array_keys(config('couriers')))],
            'tracking_number' => ['required_if:status,' . Order::STATUS_SHIPPED, 'nullable', 'string', 'max:64'],
        ];
    }

    public function messages()
    {
        return [
            'status.in' => "This order can't move to that status from \"{$this->route('order')->status}\".",
            'courier.required_if' => 'Choose the courier for this shipment.',
            'tracking_number.required_if' => 'Enter the tracking number from the courier.',
        ];
    }
}
