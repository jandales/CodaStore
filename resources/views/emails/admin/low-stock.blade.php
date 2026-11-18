@component('mail::message')
# Low-stock report

These products and sizes have {{ $threshold }} or fewer left. Restock them before they sell out.

@component('mail::table')
| Product | SKU | Left |
|:--------|:----|-----:|
@foreach ($products as $item)
| {{ ucwords($item->name) }} | {{ optional($item->sku)->sku ?: ($item->product->sku ?: '—') }} | {{ $item->qty <= 0 ? 'Sold out' : $item->qty }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('admin.inventory.filter', ['low-stock'])])
Open inventory
@endcomponent

You can change the alert level in Settings → General.

{{ config('app.name') }}
@endcomponent
