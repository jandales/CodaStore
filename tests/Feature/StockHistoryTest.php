<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Photo;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\StockMovement;
use App\Services\Orders\OrderBuilder;
use App\Services\Products\ProductSkus;
use App\Services\Products\StockLedger;
use App\Services\Products\StockMovements;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockHistoryTest extends TestCase
{
    use RefreshDatabase;

    /** A tee with sizes S (5) and M (3). */
    private function tee(): Product
    {
        $product = $this->createProduct(0, false, ['name' => 'boxy tee', 'sku' => 'TEE-1', 'regular_price' => 500]);
        app(ProductSkus::class)->sync($product, [
            ['options' => ['size' => 'S'], 'qty' => 5, 'sku' => 'TEE-1-S'],
            ['options' => ['size' => 'M'], 'qty' => 3, 'sku' => 'TEE-1-M'],
        ]);

        return $product->fresh('skus');
    }

    private function sku(Product $product, string $size): ProductSku
    {
        return $product->skus->first(fn ($s) => $s->label() === $size);
    }

    private function order(Product $product, ?ProductSku $sku, int $qty): Order
    {
        return app(OrderBuilder::class)->create([
            'lines' => [['product_id' => $product->id, 'sku_id' => optional($sku)->id, 'qty' => $qty, 'price' => 500]],
            'shipping_method_id' => 1,
            'shipping' => ['firstname' => 'Ana', 'lastname' => 'Cruz'],
            'billing' => ['email' => 'ana@example.test'],
            'payment' => ['provider' => 'cod', 'status' => 'pending'],
        ]);
    }

    public function test_a_sale_and_a_restock_are_recorded_per_variant_with_the_order()
    {
        $tee = $this->tee();
        $m = $this->sku($tee, 'M');

        $order = $this->order($tee, $m, 2);

        $sale = StockMovement::where('reason', 'sale')->first();
        $this->assertSame([$tee->id, $m->id, 'M', -2, 1, $order->id, null],
            [$sale->product_id, $sale->product_sku_id, $sale->variant_label, $sale->change, $sale->balance, $sale->order_id, $sale->admin_id]);

        foreach ($order->items as $item) {
            StockLedger::restore($item, 'cancel');
        }
        $restock = StockMovement::where('reason', 'cancel')->first();
        $this->assertSame([$m->id, 2, 3, $order->id], [$restock->product_sku_id, $restock->change, $restock->balance, $restock->order_id]);
        $this->assertSame(0, StockMovement::whereNull('product_sku_id')->count(), 'variant products keep history per variant only');
    }

    public function test_products_without_variants_keep_one_history()
    {
        $mug = $this->createProduct(10, false, ['name' => 'mug']);

        $order = $this->order($mug, null, 4);

        $sale = StockMovement::where('product_id', $mug->id)->where('reason', 'sale')->first();
        $this->assertSame([null, -4, 6, $order->id], [$sale->product_sku_id, $sale->change, $sale->balance, $sale->order_id]);
    }

    public function test_stock_in_and_out_on_the_inventory_page_record_who_did_it()
    {
        $tee = $this->tee();
        $mug = $this->createProduct(10, false, ['name' => 'mug']);
        $admin = $this->signInAdmin($this->makeAdmin(['firstname' => 'Lia', 'lastname' => 'Reyes']));

        $this->putJson(route('admin.inventory.update.variant', [$this->sku($tee, 'S')->id]), ['qty' => 4, 'action' => 1])->assertOk();
        $this->putJson(route('admin.inventory.update.variant', [$this->sku($tee, 'S')->id]), ['qty' => 20, 'action' => 0])->assertOk();
        $this->put(route('admin.inventory.update.quantity', [$mug->stock->id]), ['qty' => 3, 'action' => 0]);

        $rows = StockMovement::whereIn('reason', ['stock_in', 'stock_out'])->orderBy('id')->get();
        $this->assertSame([['stock_in', 4, 9], ['stock_out', -9, 0], ['stock_out', -3, 7]],
            $rows->map(fn ($r) => [$r->reason, $r->change, $r->balance])->all(), 'stock out never goes below 0');
        $this->assertSame([$admin->id], $rows->pluck('admin_id')->unique()->values()->all());
    }

    public function test_product_page_edits_and_removed_variants_are_recorded()
    {
        $mug = $this->createProduct(5, false, ['name' => 'mug', 'regular_price' => 500, 'sku' => 'MUG-1']);
        $this->signInAdmin();

        $this->putJson(route('admin.products.update', [$mug->encryptedId()]), [
            'name' => 'mug', 'category_id' => $mug->category_id, 'sku' => 'MUG-1', 'barcode' => '1',
            'regular_price' => 500, 'status' => 1, 'qty' => 8, 'image' => json_encode(['path' => $mug->imagePath]),
        ])->assertOk();

        $edit = StockMovement::where('reason', 'product_edit')->first();
        $this->assertSame([3, 8], [$edit->change, $edit->balance]);

        $tee = $this->tee();
        StockMovements::track($tee->id, 'product_edit', fn () => app(ProductSkus::class)->sync($tee, [
            ['options' => ['size' => 'S'], 'qty' => 6],
        ]));
        $this->assertSame(['product_edit' => 1, 'variant_removed' => -3],
            StockMovement::where('product_id', $tee->id)->whereIn('reason', ['product_edit', 'variant_removed'])->pluck('change', 'reason')->all());
        $this->assertSame('M', StockMovement::where('reason', 'variant_removed')->value('variant_label'), 'readable after the variant is gone');
    }

    public function test_stock_details_page_shows_the_history_per_variant()
    {
        $tee = $this->tee();
        $m = $this->sku($tee, 'M');
        $order = $this->order($tee, $m, 2);
        $this->signInAdmin($this->makeAdmin(['firstname' => 'Lia', 'lastname' => 'Reyes']));
        $this->putJson(route('admin.inventory.update.variant', [$this->sku($tee, 'S')->id]), ['qty' => 4, 'action' => 1]);

        $this->get(route('admin.inventory'))
            ->assertSee('title="Stock details"', false)->assertSee('title="Stock details for M"', false)
            ->assertSee('/stock?variant=' . $m->id, false);

        $this->get(route('admin.inventory.history', [$tee->encryptedId()]))->assertOk()
            ->assertSee('Stock details')->assertSee('All variants')
            ->assertSee('Sold')->assertSee('Order ' . $order->order_number)->assertSee('Stock added')->assertSee('Lia Reyes')
            ->assertSee('+4')->assertSee('−2');

        $this->get(route('admin.inventory.history', [$tee->encryptedId(), 'variant' => $m->id]))->assertOk()
            ->assertSee('Sold')->assertDontSee('Stock added')
            ->assertSeeInOrder(['Available now', '1']);
    }

    public function test_stock_details_need_the_products_permission()
    {
        $tee = $this->tee();
        $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]));

        $this->get(route('admin.inventory.history', [$tee->encryptedId()]))->assertForbidden();
    }
}
