<?php

namespace Tests\Feature;

use App\Livewire\Sales\Create as SalesCreate;
use App\Models\Account;
use App\Models\Customer;
use App\Models\GeneralSetting;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SalesStockValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        GeneralSetting::updateOrCreate(['id' => 1], [
            'allow_negative_stock' => false,
        ]);
    }

    public function test_cannot_sell_quantity_exceeding_available_stock_in_livewire_update()
    {
        $user = User::first();
        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'slug' => 'general']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $product = Product::create([
            'product_code' => 'STK-TEST-1',
            'name' => 'Stock Test Widget',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'sales_price' => 100.00,
            'purchase_price' => 50.00,
            'current_stock' => 10.00,
            'status' => true,
        ]);

        // Attempt to enter 11 when only 10 is available in stock
        Livewire::actingAs($user)
            ->test(SalesCreate::class)
            ->set('items.0.product_id', $product->id)
            ->set('items.0.quantity', 11)
            ->assertHasErrors(['items.0.quantity'])
            ->assertDispatched('toast');
    }

    public function test_cannot_sell_zero_or_negative_quantity()
    {
        $user = User::first();
        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'slug' => 'general']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $product = Product::create([
            'product_code' => 'STK-TEST-2',
            'name' => 'Zero Stock Test Item',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'sales_price' => 50.00,
            'purchase_price' => 25.00,
            'current_stock' => 5.00,
            'status' => true,
        ]);

        Livewire::actingAs($user)
            ->test(SalesCreate::class)
            ->set('items.0.product_id', $product->id)
            ->set('items.0.quantity', 0)
            ->assertHasErrors(['items.0.quantity'])
            ->assertDispatched('toast');
    }

    public function test_cannot_submit_invoice_with_quantity_exceeding_stock()
    {
        $user = User::first();
        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'slug' => 'general']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $product = Product::create([
            'product_code' => 'STK-TEST-3',
            'name' => 'Strict Backend Test Product',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'sales_price' => 60.00,
            'purchase_price' => 30.00,
            'current_stock' => 5.00,
            'status' => true,
        ]);

        $customer = Customer::first() ?? Customer::create([
            'customer_code' => 'CUST-002',
            'name' => 'Test Customer 2',
            'status' => true,
        ]);

        $account = Account::first() ?? Account::create([
            'code' => 'ACC-002',
            'name' => 'Bank Account',
            'type' => 'Bank',
            'status' => true,
        ]);

        Livewire::actingAs($user)
            ->test(SalesCreate::class)
            ->set('customer_id', $customer->id)
            ->set('payment_type', 'Cash')
            ->set('account_id', $account->id)
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'quantity' => 10, // Available is only 5
                    'unit_price' => 60.00,
                    'discount_amount' => 0,
                    'vat_percent' => 5,
                    'vat_amount' => 30.00,
                    'line_total' => 630.00,
                ]
            ])
            ->call('saveSale')
            ->assertHasErrors(['items.0.quantity'])
            ->assertDispatched('toast');
    }

    public function test_valid_sale_deducts_stock_correctly()
    {
        $user = User::first();
        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'slug' => 'general']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $product = Product::create([
            'product_code' => 'STK-TEST-4',
            'name' => 'Valid Sale Test Item',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'sales_price' => 80.00,
            'purchase_price' => 40.00,
            'weighted_cost' => 40.00,
            'current_stock' => 10.00,
            'status' => true,
        ]);

        $customer = Customer::first() ?? Customer::create([
            'customer_code' => 'CUST-003',
            'name' => 'Test Customer 3',
            'status' => true,
        ]);

        $account = Account::first() ?? Account::create([
            'code' => 'ACC-003',
            'name' => 'Main Cash',
            'type' => 'Cash',
            'status' => true,
        ]);

        Livewire::actingAs($user)
            ->test(SalesCreate::class)
            ->set('customer_id', $customer->id)
            ->set('payment_type', 'Cash')
            ->set('account_id', $account->id)
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'quantity' => 4, // 10 available - 4 = 6
                    'unit_price' => 80.00,
                    'discount_amount' => 0,
                    'vat_percent' => 5,
                    'vat_amount' => 16.00,
                    'line_total' => 336.00,
                ]
            ])
            ->call('saveSale')
            ->assertHasNoErrors()
            ->assertRedirect(route('sales.index'));

        $product->refresh();
        $this->assertEquals(6.00, (float)$product->current_stock);
    }
}
