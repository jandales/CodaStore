@component('mail::message')
# Low-stock report

These published products have {{ $threshold }} or fewer left. Restock them before they sell out.

@component('mail::table')
| Product | SKU | Left |
|:--------|:----|-----:|
@foreach ($products as $product)
| {{ ucwords($product->name) }} | {{ $product->sku ?: '—' }} | {{ $product->stockQty() <= 0 ? 'Sold out' : $product->stockQty() }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => route('admin.inventory.filter', ['low-stock'])])
Open inventory
@endcomponent

You can change the alert level in Settings → General.

{{ config('app.name') }}
@endcomponent
