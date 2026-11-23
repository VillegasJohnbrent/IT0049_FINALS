<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SalesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    private function saleData(int $productId, int $quantity = 2, array $extra = []): array
    {
        return array_merge([
            'product_id' => $productId, 'quantity' => $quantity, 'reference' => (string) Str::uuid(),
        ], $extra);
    }

    public function test_sale_uses_database_price_and_authenticated_staff(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $otherStaff = $this->staff();
        $this->post('/sales', $this->saleData($product->id, 3, [
            'customer_id' => $customer->id, 'total_price' => '0.01', 'sold_by' => $otherStaff->id,
        ]))->assertRedirect(route('sales.index'));
        $this->assertSame(7, $product->fresh()->stock_quantity);
        $sale = Sale::firstOrFail();
        $this->assertSame('37.50', $sale->total_price);
        $this->assertSame(auth()->id(), $sale->sold_by);
        $this->assertTrue($sale->customer->is($customer));
        $this->assertTrue($sale->product->is($product));
        $this->get('/sales')->assertOk()->assertSee('Notebook')->assertSee('Test Customer')
            ->assertSee('Test Staff')->assertSee('37.50')->assertSee($sale->created_at->format('M d, Y'));
    }

    public function test_walk_in_sale_and_exact_stock_quantity(): void
    {
        $product = $this->product(['stock_quantity' => 2]);
        $this->post('/sales', $this->saleData($product->id, 2, ['customer_id' => null]))
            ->assertSessionHasNoErrors();
        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertNull(Sale::firstOrFail()->customer_id);
        $this->get('/sales')->assertSee('Walk-in');
    }

    public function test_insufficient_stock_does_not_save_sale_or_change_stock(): void
    {
        $product = $this->product(['stock_quantity' => 2]);
        $this->from('/sales/create')->post('/sales', $this->saleData($product->id, 3))
            ->assertRedirect('/sales/create')
            ->assertSessionHasErrors(['quantity' => 'Insufficient stock. Only 2 available.']);
        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_later_sale_cannot_use_already_sold_stock(): void
    {
        $product = $this->product(['stock_quantity' => 3]);
        $this->post('/sales', $this->saleData($product->id, 2))->assertSessionHasNoErrors();
        $this->post('/sales', $this->saleData($product->id, 2))->assertSessionHasErrors('quantity');
        $this->assertSame(1, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 1);
    }

    public function test_zero_and_fractional_quantities_are_rejected(): void
    {
        $product = $this->product();
        foreach ([0, -1, '1.5', 'abc', 1000001] as $quantity) {
            $data = $this->saleData($product->id);
            $data['quantity'] = $quantity;
            $this->post('/sales', $data)->assertSessionHasErrors('quantity');
        }
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_missing_or_archived_product_cannot_be_sold(): void
    {
        $product = $this->product();
        $product->delete();
        $this->post('/sales', $this->saleData($product->id))->assertSessionHasErrors('product_id');
        $this->post('/sales', $this->saleData(99999))->assertSessionHasErrors('product_id');
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_deleted_customer_cannot_be_used_for_new_sale(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $customer->delete();
        $this->post('/sales', $this->saleData($product->id, 2, ['customer_id' => $customer->id]))
            ->assertSessionHasErrors('customer_id');
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_duplicate_submission_deducts_stock_once(): void
    {
        $product = $this->product(['stock_quantity' => 2]);
        $data = $this->saleData($product->id, 2);
        $this->post('/sales', $data)->assertSessionHasNoErrors();
        $this->post('/sales', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('sales', 1);
        $this->assertSame(0, $product->fresh()->stock_quantity);
    }

    public function test_reusing_reference_with_changed_details_is_rejected(): void
    {
        $product = $this->product();
        $data = $this->saleData($product->id, 2);
        $this->post('/sales', $data)->assertSessionHasNoErrors();
        $data['quantity'] = 3;
        $this->post('/sales', $data)->assertSessionHasErrors('reference');
        $this->assertDatabaseCount('sales', 1);
        $this->assertSame(8, $product->fresh()->stock_quantity);
    }

    public function test_stock_is_rolled_back_if_sale_creation_fails(): void
    {
        $product = $this->product();
        Sale::creating(function () {
            throw new RuntimeException('Insert failed.');
        });
        $this->withoutExceptionHandling();
        try {
            $this->post('/sales', $this->saleData($product->id));
            $this->fail('The sale should fail.');
        } catch (RuntimeException $error) {
            $this->assertSame('Insert failed.', $error->getMessage());
        } finally {
            Sale::flushEventListeners();
        }
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_history_preserves_original_names_prices_and_relations(): void
    {
        $product = $this->product();
        $customer = $this->customer();
        $staff = auth()->user();
        $this->post('/sales', $this->saleData($product->id, 2, ['customer_id' => $customer->id]));
        $product->update(['name' => 'Renamed Product', 'price' => '100.00']);
        $customer->update(['full_name' => 'Renamed Customer']);
        $staff->update(['full_name' => 'Renamed Staff']);
        $product->delete();
        $customer->delete();
        $staff->delete();
        $this->flushSession();
        $this->actingAs($this->staff());
        $this->get('/sales')->assertSee('Notebook')->assertSee('Test Customer')->assertSee('Test Staff')->assertSee('25.00');
        $sale = Sale::firstOrFail();
        $this->assertNotNull($sale->product);
        $this->assertNotNull($sale->customer);
        $this->assertNotNull($sale->staff);
    }

    public function test_centavo_calculation_is_exact(): void
    {
        $product = $this->product(['price' => '0.29', 'stock_quantity' => 3]);
        $this->post('/sales', $this->saleData($product->id, 3))->assertSessionHasNoErrors();
        $this->assertSame('0.87', Sale::firstOrFail()->total_price);
    }
}
