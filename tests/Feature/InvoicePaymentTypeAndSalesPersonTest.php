<?php

namespace Tests\Feature;

use App\Livewire\Purchases\Create as PurchasesCreate;
use App\Livewire\Sales\Create as SalesCreate;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoicePaymentTypeAndSalesPersonTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $customer;
    protected $supplier;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::first();

        $this->customer = Customer::create([
            'customer_code' => 'CUST-PAY-TEST',
            'name' => 'Acme Corp',
            'status' => true,
        ]);

        $this->supplier = Supplier::create([
            'supplier_code' => 'SUP-PAY-TEST',
            'name' => 'Global Imports',
            'status' => true,
        ]);

        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'Electronics', 'code' => 'ELEC']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $this->product = Product::create([
            'product_code' => 'PROD-PAY-TEST',
            'name' => 'Test Hardware',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'sales_price' => 200.00,
            'purchase_price' => 100.00,
            'current_stock' => 50.00,
            'status' => true,
        ]);

        Account::create([
            'account_number' => 'ACC-CASH-TEST',
            'name' => 'Cash on Hand',
            'type' => 'Cash',
            'opening_balance' => 1000.00,
            'current_balance' => 1000.00,
            'status' => true,
        ]);

        Account::create([
            'account_number' => 'ACC-BANK-TEST',
            'name' => 'Corporate Bank',
            'type' => 'Bank',
            'opening_balance' => 5000.00,
            'current_balance' => 5000.00,
            'status' => true,
        ]);
    }

    public function test_sales_create_defaults_payment_type_to_empty_and_requires_selection()
    {
        // Test default state is empty
        $comp = Livewire::actingAs($this->user)
            ->test(SalesCreate::class);

        $this->assertEquals('', $comp->get('payment_type'));
        $this->assertNull($comp->get('account_id'));
        $this->assertEquals('', $comp->get('sales_person'));

        // Attempting to save without selecting payment type triggers validation error
        $comp->set('customer_id', $this->customer->id)
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 2)
            ->call('saveSale')
            ->assertHasErrors(['payment_type']);
    }

    public function test_sales_create_switches_accounts_and_saves_sales_person()
    {
        Livewire::actingAs($this->user)
            ->test(SalesCreate::class)
            ->set('customer_id', $this->customer->id)
            ->set('sales_person', 'Sarah Jenkins')
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 2)
            ->set('payment_type', 'Cash')
            ->assertSet('payment_type', 'Cash')
            ->call('saveSale')
            ->assertHasNoErrors();

        $sale = Sale::where('sales_person', 'Sarah Jenkins')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('Sarah Jenkins', $sale->sales_person);
        $this->assertEquals('Cash', $sale->payment_type);
        $this->assertNotNull($sale->account_id);
    }

    public function test_purchases_create_defaults_payment_type_to_empty_and_requires_selection()
    {
        // Test default state is empty
        $comp = Livewire::actingAs($this->user)
            ->test(PurchasesCreate::class);

        $this->assertEquals('', $comp->get('payment_type'));
        $this->assertNull($comp->get('account_id'));
        $this->assertEquals('', $comp->get('sales_person'));

        // Attempting to save without selecting payment type triggers validation error
        $comp->set('supplier_id', $this->supplier->id)
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 5)
            ->set('items.0.unit_price', 100)
            ->call('savePurchase')
            ->assertHasErrors(['payment_type']);
    }

    public function test_purchases_create_switches_accounts_and_saves_sales_person()
    {
        Livewire::actingAs($this->user)
            ->test(PurchasesCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('reference_number', 'PO-998811')
            ->set('sales_person', 'David Miller')
            ->set('items.0.product_id', $this->product->id)
            ->set('items.0.quantity', 5)
            ->set('items.0.unit_price', 100)
            ->set('payment_type', 'Credit')
            ->call('savePurchase')
            ->assertHasNoErrors();

        $purchase = Purchase::where('sales_person', 'David Miller')->first();
        $this->assertNotNull($purchase);
        $this->assertEquals('David Miller', $purchase->sales_person);
        $this->assertEquals('Credit', $purchase->payment_type);
        $this->assertNull($purchase->account_id);
    }
}
