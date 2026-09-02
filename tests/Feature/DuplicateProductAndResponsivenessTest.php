<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DuplicateProductAndResponsivenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sales_invoice_prevents_duplicate_products(): void
    {
        $user = User::first();
        $customer = Customer::create(['customer_code' => 'C-TEST-1', 'name' => 'Test Customer']);
        $p1 = Product::create([
            'product_code' => 'P1',
            'name' => 'Product 1',
            'purchase_price' => 50,
            'sales_price' => 100,
            'weighted_cost' => 50,
            'current_stock' => 100,
        ]);
        $p2 = Product::create([
            'product_code' => 'P2',
            'name' => 'Product 2',
            'purchase_price' => 40,
            'sales_price' => 80,
            'weighted_cost' => 40,
            'current_stock' => 100,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Sales\Create::class)
            ->assertSet('items.0.product_id', '');

        // Select first product on row 0
        $component->set('items.0.product_id', $p1->id);
        $component->assertSet('items.0.product_id', $p1->id);

        // Add second item -> should show Select Product (empty product_id)
        $component->call('addItem');
        $component->assertSet('items.1.product_id', '');

        // Now attempt to change row 1's product to p1 (duplicate of row 0)
        $component->set('items.1.product_id', $p1->id);

        // It should detect the duplicate, reset row 1, and dispatch warning toast
        $component->assertSet('items.1.product_id', '')
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'warning' && str_contains($params['message'], 'Duplicate products are not allowed');
            });
    }

    public function test_sales_create_shows_select_product_initially_and_on_adding_item(): void
    {
        $user = User::first();
        $p1 = Product::create([
            'product_code' => 'TEST-INIT-P1',
            'name' => 'Initial Test Product 1',
            'purchase_price' => 50,
            'sales_price' => 100,
            'weighted_cost' => 50,
            'current_stock' => 100,
        ]);
        $p2 = Product::create([
            'product_code' => 'TEST-INIT-P2',
            'name' => 'Initial Test Product 2',
            'purchase_price' => 40,
            'sales_price' => 80,
            'weighted_cost' => 40,
            'current_stock' => 100,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Sales\Create::class)
            ->assertSet('items.0.product_id', '')
            ->assertSee('Select Product...');

        // Calling addItem before selecting product fails validation and does not add line
        $component->call('addItem')
            ->assertHasErrors(['items.0.product_id'])
            ->assertCount('items', 1);

        // Select product
        $component->set('items.0.product_id', $p1->id);

        // Now addItem succeeds and creates row 1 with empty product_id
        $component->call('addItem')
            ->assertSet('items.1.product_id', '')
            ->assertCount('items', 2);
    }

    public function test_purchase_invoice_prevents_duplicate_products(): void
    {
        $user = User::first();
        $supplier = Supplier::create(['supplier_code' => 'S-TEST-1', 'name' => 'Test Supplier']);
        $p1 = Product::create([
            'product_code' => 'PUR-P1',
            'name' => 'Purchase Product 1',
            'purchase_price' => 30,
            'sales_price' => 60,
            'weighted_cost' => 30,
            'current_stock' => 50,
        ]);
        $p2 = Product::create([
            'product_code' => 'PUR-P2',
            'name' => 'Purchase Product 2',
            'purchase_price' => 20,
            'sales_price' => 40,
            'weighted_cost' => 20,
            'current_stock' => 50,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Purchases\Create::class)
            ->assertSet('items.0.product_id', '');

        // Select first product
        $component->set('items.0.product_id', $p1->id);
        $component->assertSet('items.0.product_id', $p1->id);

        // Add second item -> should show empty product_id (select product)
        $component->call('addItem');
        $component->assertSet('items.1.product_id', '');

        // Attempt to set row 1's product to p1 (duplicate of row 0)
        $component->set('items.1.product_id', $p1->id);

        // It should reset row 1 and dispatch warning toast
        $component->assertSet('items.1.product_id', '')
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'warning' && str_contains($params['message'], 'Duplicate products are not allowed');
            });
    }

    public function test_purchase_create_shows_select_product_initially_and_on_adding_item(): void
    {
        $user = User::first();
        $p1 = Product::create([
            'product_code' => 'TEST-PUR-INIT-1',
            'name' => 'Purchase Init Test 1',
            'purchase_price' => 30,
            'sales_price' => 60,
            'weighted_cost' => 30,
            'current_stock' => 50,
        ]);
        $p2 = Product::create([
            'product_code' => 'TEST-PUR-INIT-2',
            'name' => 'Purchase Init Test 2',
            'purchase_price' => 25,
            'sales_price' => 50,
            'weighted_cost' => 25,
            'current_stock' => 50,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Purchases\Create::class)
            ->assertSet('items.0.product_id', '')
            ->assertSee('Select Product...');

        // Cannot add empty item before selecting product
        $component->call('addItem')
            ->assertHasErrors(['items.0.product_id'])
            ->assertCount('items', 1);

        // Select product
        $component->set('items.0.product_id', $p1->id);

        // Now adding item creates row 1 with empty product_id
        $component->call('addItem')
            ->assertSet('items.1.product_id', '')
            ->assertCount('items', 2);
    }

    public function test_sales_create_view_has_add_item_button_below_items_table(): void
    {
        $user = User::first();
        $html = Livewire::actingAs($user)
            ->test(\App\Livewire\Sales\Create::class)
            ->html();

        // Check that Add Line Item exists and is rendered
        $this->assertStringContainsString('Add Line Item', $html);
        $this->assertStringContainsString('addItem', $html);

        // Check that table closing tag or items list occurs BEFORE the Add Line Item button
        $tablePos = strpos($html, 'table-responsive');
        $buttonPos = strpos($html, 'wire:click="addItem"');
        $this->assertNotFalse($tablePos);
        $this->assertNotFalse($buttonPos);
        $this->assertGreaterThan($tablePos, $buttonPos, 'The Add Line Item button must appear after/below the items table.');
    }

    public function test_purchase_create_view_has_add_item_button_below_items_table(): void
    {
        $user = User::first();
        $html = Livewire::actingAs($user)
            ->test(\App\Livewire\Purchases\Create::class)
            ->html();

        $this->assertStringContainsString('Add Line Item', $html);
        $this->assertStringContainsString('addItem', $html);

        $tablePos = strpos($html, 'table-responsive');
        $buttonPos = strpos($html, 'wire:click="addItem"');
        $this->assertNotFalse($tablePos);
        $this->assertNotFalse($buttonPos);
        $this->assertGreaterThan($tablePos, $buttonPos, 'The Add Line Item button must appear after/below the items table.');
    }

    public function test_modal_component_structure_supports_scrolling(): void
    {
        $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
        $modalComponent = file_get_contents(resource_path('views/components/modal.blade.php'));

        // Verify modal-dialog-scrollable is applied to modal dialog
        $this->assertStringContainsString('modal-dialog-scrollable', $modalComponent);

        // Verify flex-column and scroll rules are in app layout CSS
        $this->assertStringContainsString('.modal-dialog-scrollable', $appLayout);
        $this->assertStringContainsString('overflow-y: auto', $appLayout);
        $this->assertStringContainsString('.modal-dialog-scrollable .modal-content > form', $appLayout);
    }
}
