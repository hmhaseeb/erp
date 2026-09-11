<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InventoryTableResponsivenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_inventory_page_renders_desktop_and_mobile_responsive_elements(): void
    {
        $user = User::first();
        $category = ProductCategory::first() ?? ProductCategory::create(['name' => 'Smartphones']);
        $unit = Unit::first() ?? Unit::create(['name' => 'Pcs', 'short_code' => 'pcs']);

        $product = Product::create([
            'product_code' => 'TEST-RESP-001',
            'barcode' => '8800112233',
            'name' => 'Samsung Galaxy S24 Ultra Titanium Black',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'brand' => 'Samsung',
            'purchase_price' => 3200.00,
            'sales_price' => 3999.00,
            'weighted_cost' => 3200.00,
            'current_stock' => 25.00,
            'min_stock' => 5.00,
            'status' => true,
            'warehouse' => 'Central Hub',
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Products\Index::class);

        $html = $component->html();

        // 1. Desktop 10-column table assertions
        $this->assertStringContainsString('d-none d-md-table', $html);
        $this->assertStringContainsString('SKU / Code', $html);
        $this->assertStringContainsString('Product Details', $html);
        $this->assertStringContainsString('Category', $html);
        $this->assertStringContainsString('Unit', $html);
        $this->assertStringContainsString('Purchase Cost', $html);
        $this->assertStringContainsString('Retail Price', $html);
        $this->assertStringContainsString('Avg Cost', $html);
        $this->assertStringContainsString('Current Stock', $html);
        $this->assertStringContainsString('Status', $html);
        $this->assertStringContainsString('Actions', $html);

        // 2. Mobile Responsive Card List assertions (<768px)
        $this->assertStringContainsString('d-md-none', $html);
        $this->assertStringContainsString('inventory-mobile-card', $html);
        $this->assertStringContainsString('inventory-card-header', $html);
        $this->assertStringContainsString('inventory-card-title', $html);
        $this->assertStringContainsString('inventory-card-metrics', $html);

        // 3. Prioritized mobile data
        $this->assertStringContainsString('Samsung Galaxy S24 Ultra Titanium Black', $html);
        $this->assertStringContainsString('TEST-RESP-001', $html);
        $this->assertStringContainsString($category->name, $html);
        $this->assertStringContainsString('In Stock', $html);

        // 4. Secondary collapsed data in expandable details row
        $this->assertStringContainsString('View Details & Costs', $html);
        $this->assertStringContainsString('inventory-detail-box', $html);
        $this->assertStringContainsString('Average Cost:', $html);
        $this->assertStringContainsString('Unit of Measure:', $html);
        $this->assertStringContainsString('Min Stock Alert:', $html);
        $this->assertStringContainsString('8800112233', $html);

        // 5. Touch-friendly action buttons
        $this->assertStringContainsString('inventory-card-actions', $html);
        $this->assertStringContainsString('showProductDetails(' . $product->id . ')', $html);
        $this->assertStringContainsString('editProduct(' . $product->id . ')', $html);
        $this->assertStringContainsString('deleteProduct(' . $product->id . ')', $html);
    }

    public function test_mobile_sorting_controls_trigger_sorting(): void
    {
        $user = User::first();

        Product::create([
            'product_code' => 'AAA-001',
            'name' => 'Apple iPhone 15',
            'purchase_price' => 3000,
            'sales_price' => 3500,
            'weighted_cost' => 3000,
            'current_stock' => 10,
        ]);

        Product::create([
            'product_code' => 'ZZZ-999',
            'name' => 'Zebra Label Printer',
            'purchase_price' => 800,
            'sales_price' => 1200,
            'weighted_cost' => 800,
            'current_stock' => 50,
        ]);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\Products\Index::class)
            ->set('sortField', 'name')
            ->call('sortBy', 'name');

        $this->assertEquals('name', $component->get('sortField'));
        $this->assertEquals('asc', $component->get('sortDirection'));

        // Toggle sort direction back to desc
        $component->call('sortBy', 'name');
        $this->assertEquals('desc', $component->get('sortDirection'));
    }

    public function test_inventory_filter_and_search_still_work(): void
    {
        $user = User::first();
        $catA = ProductCategory::create(['name' => 'Laptops']);
        $catB = ProductCategory::create(['name' => 'Headphones']);

        $p1 = Product::create([
            'product_code' => 'LAPTOP-01',
            'name' => 'Dell XPS 15',
            'category_id' => $catA->id,
            'purchase_price' => 4000,
            'sales_price' => 5000,
            'weighted_cost' => 4000,
            'current_stock' => 15,
        ]);

        $p2 = Product::create([
            'product_code' => 'AUDIO-01',
            'name' => 'Sony WH-1000XM5',
            'category_id' => $catB->id,
            'purchase_price' => 900,
            'sales_price' => 1200,
            'weighted_cost' => 900,
            'current_stock' => 0, // Out of stock
        ]);

        // Search test
        Livewire::actingAs($user)
            ->test(\App\Livewire\Products\Index::class)
            ->set('search', 'Dell')
            ->assertSee('Dell XPS 15')
            ->assertDontSee('Sony WH-1000XM5');

        // Stock status filter test
        Livewire::actingAs($user)
            ->test(\App\Livewire\Products\Index::class)
            ->set('stock_status_filter', 'out_of_stock')
            ->assertSee('Sony WH-1000XM5')
            ->assertDontSee('Dell XPS 15');
    }

    public function test_view_modal_opens_from_action_button(): void
    {
        $user = User::first();
        $p = Product::create([
            'product_code' => 'MODAL-TEST',
            'name' => 'Modal Test Product',
            'purchase_price' => 100,
            'sales_price' => 150,
            'weighted_cost' => 100,
            'current_stock' => 20,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Products\Index::class)
            ->assertSet('viewProduct', null)
            ->call('showProductDetails', $p->id)
            ->assertSet('viewProduct.id', $p->id)
            ->assertSee('Modal Test Product');
    }
}
