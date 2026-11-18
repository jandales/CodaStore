<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Admin > Orders > Create order. Prices and totals are worked out on the server, not taken from here. */
class ManualOrderRequest extends FormRequest
{
    public const MAX_BACKDATE_DAYS = 30;

    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'country' => $this->input('country') ?: 'Philippines',
            'email' => $this->input('email') ? mb_strtolower(trim($this->input('email'))) : null,
            'order_date' => $this->input('order_date') ?: now()->toDateString(),
        ]);
    }

    public function rules()
    {
        $newCustomer = $this->input('customer_mode') === 'new';

        return [
            'customer_mode' => ['required', Rule::in(['existing', 'new'])],
            'user_id' => ['exclude_unless:customer_mode,existing', 'required', 'exists:users,id'],
            // New customer: with an email an account is created; without one it's a guest order.
            'email' => ['nullable', 'email:filter', 'max:191', $newCustomer ? 'unique:users,email' : 'nullable'],

            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'street' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'zipcode' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],

            'lines' => ['required', 'array', 'min:1', 'max:50'],
            'lines.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'lines.*.sku_id' => ['nullable', 'integer', 'exists:product_skus,id'],
            'lines.*.qty' => ['required', 'integer', 'min:1', 'max:1000'],

            'shipping_method_id' => ['required', 'exists:shipping_methods,id'],
            'shipping_charge' => ['required', 'numeric', 'min:0', 'max:100000'],
            'discount_type' => ['nullable', Rule::in(['amount', 'percent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:1000000'],

            'payment' => ['required', Rule::in(['cod', 'paid', 'awaiting'])],
            'payment_method' => ['required_if:payment,paid', 'nullable', Rule::in(array_keys(OrderPayment::MANUAL_METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:100'],

            'channel' => ['required', Rule::in(array_keys(Order::CHANNELS))],
            'order_date' => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . now()->subDays(self::MAX_BACKDATE_DAYS)->toDateString()],
            'note' => ['nullable', 'string', 'max:2000'],
            'send_email' => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'Choose the customer, or switch to "New customer".',
            'email.unique' => 'A customer with this email already exists. Choose them under "Existing customer".',
            'lines.required' => 'Add at least one item.',
            'lines.min' => 'Add at least one item.',
            'payment_method.required_if' => 'Choose how the customer paid.',
            'order_date.before_or_equal' => "The order date can't be in the future.",
            'order_date.after_or_equal' => 'The order date can be at most ' . self::MAX_BACKDATE_DAYS . ' days ago.',
            'required' => 'This field is required.',
        ];
    }
}
