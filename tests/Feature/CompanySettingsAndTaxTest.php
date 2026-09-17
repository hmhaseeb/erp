<?php

namespace Tests\Feature;

use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Purchases\Create as PurchasesCreate;
use App\Livewire\Sales\Create as SalesCreate;
use App\Livewire\Settings\CompanySettings;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanySettingsAndTaxTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->user = User::first();

        SettingsService::clearCache();

        // Ensure company settings exist
        CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'Apex General Trading LLC',
                'country' => 'United Arab Emirates',
                'currency' => 'AED',
                'currency_symbol' => 'AED',
                'default_vat_percent' => 5.00,
            ]
        );
    }

    public function test_company_settings_saves_currency_country_and_vat()
    {
        $this->actingAs($this->user);

        Livewire::test(CompanySettings::class)
            ->set('company_name', 'Bharat Tech Solutions')
            ->set('country', 'India')
            ->set('currency', 'INR')
            ->set('currency_symbol', '₹')
            ->set('default_vat_percent', 18.00)
            ->set('trn_number', '27AAPFU0939F1ZV')
            ->call('saveSettings')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $this->assertDatabaseHas('company_settings', [
            'country' => 'India',
            'currency' => 'INR',
            'default_vat_percent' => 18.00,
            'trn_number' => '27AAPFU0939F1ZV',
        ]);

        // Helpers and service should reflect changes
        $this->assertEquals('INR', currency());
        $this->assertEquals('GST', tax_name());
        $this->assertEquals('GSTIN', tax_number_name());
        $this->assertEquals(18.00, default_tax_percent());
    }

    public function test_company_settings_preset_switching()
    {
        $this->actingAs($this->user);

        Livewire::test(CompanySettings::class)
            ->set('selected_preset', 'INR')
            ->assertSet('country', 'India')
            ->assertSet('currency', 'INR')
            ->assertSet('default_vat_percent', 18.00)
            ->set('selected_preset', 'AED')
            ->assertSet('country', 'United Arab Emirates')
            ->assertSet('currency', 'AED')
            ->assertSet('default_vat_percent', 5.00);
    }

    public function test_default_tax_percent_affects_products_creation()
    {
        $this->actingAs($this->user);

        // Update default tax to 18%
        CompanySetting::first()->update([
            'default_vat_percent' => 18.00,
            'country' => 'India',
            'currency' => 'INR',
        ]);
        SettingsService::clearCache();

        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'code' => 'GEN']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $test = Livewire::test(ProductsIndex::class);
        $this->assertEquals(18.00, (float)$test->get('tax_percent'));

        $test->call('openModal');
        $this->assertEquals(18.00, (float)$test->get('tax_percent'));

        $test->set('product_code', 'TEST-TAX-PROD-01')
            ->set('name', 'India GST Product')
            ->set('category_id', $cat->id)
            ->set('unit_id', $unit->id)
            ->set('purchase_price', 100)
            ->set('sales_price', 150)
            ->call('saveProduct')
            ->assertHasNoErrors();

        $savedProduct = Product::where('product_code', 'TEST-TAX-PROD-01')->first();
        $this->assertNotNull($savedProduct);
        $this->assertEquals(18.00, (float)$savedProduct->tax_percent);
    }

    public function test_default_tax_percent_affects_sales_and_purchases()
    {
        $this->actingAs($this->user);

        CompanySetting::first()->update([
            'default_vat_percent' => 12.50,
            'country' => 'India',
            'currency' => 'INR',
        ]);
        SettingsService::clearCache();

        $cat = ProductCategory::first() ?? ProductCategory::create(['name' => 'General', 'code' => 'GEN']);
        $unit = Unit::first() ?? Unit::create(['name' => 'PCS', 'code' => 'PCS']);

        $customer = Customer::create([
            'customer_code' => 'CUST-TAX-01',
            'name' => 'Customer Tax Test',
            'status' => true,
        ]);

        $supplier = Supplier::create([
            'supplier_code' => 'SUP-TAX-01',
            'name' => 'Supplier Tax Test',
            'status' => true,
        ]);

        $productWithoutCustomTax = Product::create([
            'product_code' => 'PROD-NO-TAX-01',
            'name' => 'No Custom Tax Product',
            'category_id' => $cat->id,
            'unit_id' => $unit->id,
            'purchase_price' => 50,
            'sales_price' => 100,
            'tax_percent' => 12.50,
            'current_stock' => 50,
            'status' => true,
        ]);

        // Sales Create
        $salesTest = Livewire::test(SalesCreate::class);
        $salesItems = $salesTest->get('items');
        $this->assertNotEmpty($salesItems);
        $this->assertEquals(12.50, (float)$salesItems[0]['vat_percent']);

        // Purchases Create
        $purchasesTest = Livewire::test(PurchasesCreate::class);
        $purchaseItems = $purchasesTest->get('items');
        $this->assertNotEmpty($purchaseItems);
        $this->assertEquals(12.50, (float)$purchaseItems[0]['vat_percent']);
    }

    public function test_country_tax_labels_uae_vs_india()
    {
        // UAE Case
        CompanySetting::first()->update([
            'country' => 'United Arab Emirates',
            'currency' => 'AED',
        ]);
        SettingsService::clearCache();

        $this->assertEquals('VAT', tax_name());
        $this->assertEquals('TRN', tax_number_name());

        // India Case
        CompanySetting::first()->update([
            'country' => 'India',
            'currency' => 'INR',
        ]);
        SettingsService::clearCache();

        $this->assertEquals('GST', tax_name());
        $this->assertEquals('GSTIN', tax_number_name());
    }
}
