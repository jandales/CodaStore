<?php

namespace Tests\Feature;

use App\Exceptions\OutOfStockException;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\StockMovement;
use App\Services\Orders\OrderBuilder;
use App\Services\Products\ProductSkus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DamagedStockTest extends TestCase
{
    use RefreshDatabase;

    /** A jacket in size M with 100 available. */
    private function jacket(): array
    {
        $product = $this->createProduct(0, false, ['name' => 'denim jacket', 'sku' => 'JKT-1', 'regular_price' => 1000]);
        app(ProductSkus::class)->sync($product, [['options' => ['size' => 'M'], 'qty' => 100, 'sku' => 'JKT-1-M']]);
        $product = $product->fresh('skus');

        return [$product, $product->skus->first()];
    }

    private function adjust(Product $product, ?ProductSku $sku, string $action, int $qty, ?string $note = null)
    {
        return $this->putJson(route('admin.inventory.adjust', [$product->encryptedId()]), array_filter([
            'action' => $action, 'qty' => $qty, 'sku_id' => optional($sku)->id, 'note' => $note,
        ], fn ($v) => $v !== null));
    }

    public function test_100_on_hand_with_5_damaged_leaves_95_to_sell()
    {
        [$jacket, $m] = $this->jacket();
        $admin = $this->signInAdmin($this->makeAdmin(['firstname' => 'Lia', 'lastname' => 'Reyes']));

        $this->adjust($jacket, $m, 'damage', 5, 'Torn seam, supplier box 3')->assertOk()
            ->assertJson(['stock' => ['available' => 95, 'damaged' => 5, 'on_hand' => 100]]);

        $m->refresh();
        $this->assertSame([95, 5], [$m->qty, $m->damaged_qty]);
        $this->assertSame(95, $jacket->fresh()->stockQty(), 'the shop sells 95');
        $this->assertSame(5, $jacket->fresh()->damagedQty());

        $move = StockMovement::where('reason', 'damaged')->first();
        $this->assertSame([-5, 95, 5, 5, 'Torn seam, supplier box 3', $admin->id],
            [$move->change, $move->balance, $move->damaged_change, $move->damaged_balance, $move->note, $move->admin_id]);
    }

    public function test_repaired_goes_back_to_stock_and_write_off_removes_for_good()
    {
        [$jacket, $m] = $this->jacket();
        $this->signInAdmin();
        $this->adjust($jacket, $m, 'damage', 5);

        $this->adjust($jacket, $m, 'repaired', 2)->assertJson(['stock' => ['available' => 97, 'damaged' => 3]]);
        $this->adjust($jacket, $m, 'write_off', 3)->assertJson(['stock' => ['available' => 97, 'damaged' => 0, 'on_hand' => 97]]);

        $this->assertSame(
            [['damaged', -5, 5], ['repaired', 2, -2], ['written_off', 0, -3]],
            StockMovement::whereIn('reason', ['damaged', 'repaired', 'written_off'])->orderBy('id')->get()
                ->map(fn ($r) => [$r->reason, $r->change, $r->damaged_change])->all()
        );
    }

    public function test_quantities_above_what_is_there_are_refused_with_a_clear_message()
    {
        [$jacket, $m] = $this->jacket();
        $this->signInAdmin();

        $this->adjust($jacket, $m, 'damage', 101)->assertStatus(422)->assertJsonValidationErrors(['qty' => 'Only 100 available to mark as damaged.']);
        $this->adjust($jacket, $m, 'write_off', 1)->assertStatus(422)->assertJsonValidationErrors(['qty' => 'Only 0 damaged to write off.']);
        $this->adjust($jacket, $m, 'stock_out', 101)->assertStatus(422)->assertJsonValidationErrors(['qty' => 'Only 100 available.']);
        $this->adjust($jacket, $m, 'damage', 0)->assertStatus(422)->assertJsonValidationErrors('qty');
        $this->adjust($jacket, null, 'damage', 1)->assertStatus(422)->assertJsonValidationErrors(['sku_id' => 'Choose a variant.']);
        $this->adjust($jacket, $m, 'steal', 1)->assertStatus(422)->assertJsonValidationErrors('action');

        $this->assertSame([100, 0], [$m->fresh()->qty, $m->fresh()->damaged_qty]);
        $this->assertSame(0, StockMovement::whereIn('reason', ['damaged', 'written_off', 'stock_out'])->count());
    }

    public function test_stock_in_and_out_and_products_without_variants()
    {
        $mug = $this->createProduct(10, false, ['name' => 'mug']);
        $this->signInAdmin();

        $this->adjust($mug, null, 'stock_in', 5, 'New delivery')->assertJson(['stock' => ['available' => 15]]);
        $this->adjust($mug, null, 'damage', 2)->assertJson(['stock' => ['available' => 13, 'damaged' => 2]]);
        $this->adjust($mug, null, 'stock_out', 3)->assertJson(['stock' => ['available' => 10, 'damaged' => 2]]);

        $this->assertSame([10, 2], [$mug->fresh()->stockQty(), $mug->fresh()->damagedQty()]);
        $this->assertSame(['stock_in', 'damaged', 'stock_out'], StockMovement::where('product_id', $mug->id)->whereNull('product_sku_id')->orderBy('id')->pluck('reason')->all());
    }

    public function test_damaged_units_can_never_be_sold()
    {
        [$jacket, $m] = $this->jacket();
        $this->signInAdmin();
        $this->adjust($jacket, $m, 'damage', 98);

        $this->expectException(OutOfStockException::class);
        app(OrderBuilder::class)->create([
            'lines' => [['product_id' => $jacket->id, 'sku_id' => $m->id, 'qty' => 3, 'price' => 1000]],
            'shipping_method_id' => 1, 'shipping' => [], 'billing' => [], 'payment' => ['provider' => 'cod', 'status' => 'pending'],
        ]);
    }

    public function test_product_page_edits_keep_damaged_units()
    {
        $mug = $this->createProduct(10, false, ['name' => 'mug', 'regular_price' => 500, 'sku' => 'MUG-1']);
        $this->signInAdmin();
        $this->adjust($mug, null, 'damage', 4);

        $this->get(route('admin.products.edit', [$mug->encryptedId()]))->assertSee('4 damaged units are kept apart');

        $this->putJson(route('admin.products.update', [$mug->encryptedId()]), [
            'name' => 'mug', 'category_id' => $mug->category_id, 'sku' => 'MUG-1', 'barcode' => '1',
            'regular_price' => 500, 'status' => 1, 'qty' => 20, 'image' => json_encode(['path' => $mug->imagePath]),
        ])->assertOk();

        $this->assertSame([20, 4], [$mug->fresh()->stockQty(), $mug->fresh()->damagedQty()]);
    }

    public function test_inventory_and_stock_details_show_damaged_stock()
    {
        [$jacket, $m] = $this->jacket();
        $other = $this->createProduct(5, false, ['name' => 'plain cap']);
        $this->signInAdmin();
        $this->adjust($jacket, $m, 'damage', 5, 'Water damage');

        $this->get(route('admin.inventory'))->assertOk()
            ->assertSee('Damaged')->assertSee('Adjust stock')->assertSee('data-damaged="5"', false)->assertSee('Has damaged stock')
            ->assertSeeInOrder(['>Quantity</th>', '>Damaged</th>', '>Available</th>'], false)
            ->assertSeeInOrder(['data-total>100<', 'data-damaged>5<', 'data-available>95<'], false);
        $this->get(route('admin.inventory.filter', ['damaged']))->assertOk()
            ->assertSee('denim jacket')->assertDontSee('plain cap');

        $this->get(route('admin.inventory.history', [$jacket->encryptedId(), 'variant' => $m->id]))->assertOk()
            ->assertSeeInOrder(['Available now', '95', 'Damaged now', '5', 'On hand', '100'])
            ->assertSee('Marked as damaged')->assertSee('Water damage')->assertSee('5 damaged');

        $products = $this->get(route('admin.exports.products'))->getContent();
        $this->assertStringContainsString('Damaged', $products);
        $this->assertStringContainsString('M:95,,5', $products);
    }

    public function test_adjusting_stock_needs_the_products_permission()
    {
        [$jacket, $m] = $this->jacket();
        $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]));

        $this->adjust($jacket, $m, 'damage', 1)->assertForbidden();
        $this->assertSame(0, (int) $m->fresh()->damaged_qty);
    }
}
