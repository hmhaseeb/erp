<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickModalsAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sales_create_quick_customer_modal_workflow(): void
    {
        $user = User::first();

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Sales\Create::class);

        // Initially customer modal is closed
        $component->assertSet('isCustomerModalOpen', false)
            ->assertDontSee('Save & Select Customer');

        // Open Customer Modal
        $component->call('openCustomerModal')
            ->assertSet('isCustomerModalOpen', true)
            ->assertSee('Save & Select Customer');

        // Fill and save new customer
        $component->set('cust_name', 'Acme Corporation')
            ->set('cust_company_name', 'Acme Global LLC')
            ->set('cust_mobile', '+971501112233')
            ->set('cust_email', 'billing@acme.com')
            ->set('cust_opening_balance', 500)
            ->call('saveNewCustomer');

        // Verify Customer was created in DB
        $created = Customer::where('name', 'Acme Corporation')->first();
        $this->assertNotNull($created);
        $this->assertEquals('Acme Global LLC', $created->company_name);

        // Verify modal closed, toast dispatched, and customer_id updated to the new customer
        $component->assertSet('isCustomerModalOpen', false)
            ->assertSet('customer_id', $created->id)
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'success' && str_contains($params['message'], 'Acme Corporation');
            });
    }

    public function test_sales_create_does_not_show_add_new_product_button(): void
    {
        $user = User::first();

        $html = Livewire::actingAs($user)
            ->test(\App\Livewire\Sales\Create::class)
            ->html();

        // Ensure Sales Create does not have the Add New Product button
        $this->assertStringNotContainsString('Add New Product', $html);
        $this->assertStringNotContainsString('openProductModal', $html);
    }

    public function test_purchase_create_quick_supplier_modal_workflow(): void
    {
        $user = User::first();

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Purchases\Create::class);

        // Initially modal is closed
        $component->assertSet('isSupplierModalOpen', false)
            ->assertDontSee('Save & Select Supplier');

        // Open Supplier Modal
        $component->call('openSupplierModal')
            ->assertSet('isSupplierModalOpen', true)
            ->assertSee('Save & Select Supplier');

        // Fill and save new supplier
        $component->set('supp_name', 'Global Parts Direct')
            ->set('supp_company_name', 'Global Parts FZ LLC')
            ->set('supp_mobile', '+971509998877')
            ->set('supp_email', 'orders@globalparts.com')
            ->set('supp_opening_balance', 1200)
            ->call('saveNewSupplier');

        // Verify Supplier was created in DB
        $supplier = Supplier::where('name', 'Global Parts Direct')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('Global Parts FZ LLC', $supplier->company_name);

        // Verify modal closed, toast dispatched, and supplier_id updated to the new supplier
        $component->assertSet('isSupplierModalOpen', false)
            ->assertSet('supplier_id', $supplier->id)
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'success' && str_contains($params['message'], 'Global Parts Direct');
            });
    }

    public function test_purchase_create_quick_product_modal_reuses_standard_product_form(): void
    {
        $user = User::first();
        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'Hardware', 'slug' => 'hardware']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Units', 'code' => 'unt']);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Purchases\Create::class);

        // Initially modal is closed
        $component->assertSet('isProductModalOpen', false)
            ->assertDontSee('Save & Select Product');

        // Open Product Modal
        $component->call('openProductModal')
            ->assertSet('isProductModalOpen', true)
            ->assertSee('Save & Select Product');

        // Fill and save new product using standard Product form fields
        $component->set('name', 'Heavy Duty Caster')
            ->set('brand', 'Industrial Pro')
            ->set('category_id', $cat->id)
            ->set('unit_id', $unit->id)
            ->set('purchase_price', 45)
            ->set('sales_price', 85)
            ->set('tax_percent', 5)
            ->call('saveNewProduct');

        // Verify Product created in DB
        $product = Product::where('name', 'Heavy Duty Caster')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Industrial Pro', $product->brand);
        $this->assertEquals(45, (float)$product->purchase_price);

        // Verify modal closed, toast dispatched, and item row populated with purchase cost
        $component->assertSet('isProductModalOpen', false)
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'success' && str_contains($params['message'], 'Heavy Duty Caster');
            });

        $items = $component->get('items');
        $productIds = array_column($items, 'product_id');
        $this->assertContains($product->id, $productIds);
    }
}
