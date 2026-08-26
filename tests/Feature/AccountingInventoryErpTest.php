<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use App\Services\SalesService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingInventoryErpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_opening_stock_initialization(): void
    {
        $stockService = app(StockService::class);
        $product = Product::create([
            'product_code' => 'TEST-001',
            'name' => 'Test Item 1',
            'purchase_price' => 100.00,
            'sales_price' => 150.00,
            'weighted_cost' => 100.00,
            'current_stock' => 0,
        ]);

        $stockService->recordMovement(
            $product->id,
            now()->toDateString(),
            'OPENING',
            100.0,
            0,
            100.00,
            Product::class,
            $product->id,
            'Opening Stock'
        );

        $product->refresh();
        $this->assertEquals(100.00, $product->current_stock);
        $this->assertEquals(100.00, $product->weighted_cost);
    }

    public function test_purchase_and_weighted_average_cost(): void
    {
        $purchaseService = app(PurchaseService::class);

        $supplier = Supplier::create([
            'supplier_code' => 'SUP-TEST',
            'name' => 'Test Supplier',
            'opening_balance' => 0,
            'current_balance' => 0,
        ]);

        $product = Product::create([
            'product_code' => 'TEST-002',
            'name' => 'Widget A',
            'purchase_price' => 10.00,
            'sales_price' => 20.00,
            'weighted_cost' => 10.00,
            'current_stock' => 100,
        ]);

        // Purchase 50 units @ AED 20 each on Credit
        $headerCredit = [
            'purchase_number' => 'PUR-000001',
            'purchase_date' => now()->toDateString(),
            'supplier_id' => $supplier->id,
            'payment_type' => 'Credit',
            'subtotal' => 1000.00,
            'vat_amount' => 50.00,
            'grand_total' => 1050.00,
        ];
        $itemsCredit = [
            [
                'product_id' => $product->id,
                'quantity' => 50,
                'unit_price' => 20.00,
                'vat_percent' => 5,
                'vat_amount' => 50.00,
                'line_total' => 1050.00,
            ]
        ];

        $purchaseService->createPurchase($headerCredit, $itemsCredit);

        $product->refresh();
        $supplier->refresh();

        // Stock = 100 + 50 = 150 units
        $this->assertEquals(150.00, $product->current_stock);
        // Weighted Average Cost = (100 * 10 + 50 * 20) / 150 = 2000 / 150 = 13.3333
        $this->assertEquals(13.3333, round($product->weighted_cost, 4));
        // Supplier Payable = AED 1050.00
        $this->assertEquals(1050.00, $supplier->current_balance);
    }

    public function test_cash_sale_and_cogs_calculation(): void
    {
        $salesService = app(SalesService::class);
        $cashAccount = Account::where('type', 'Cash')->first();
        $initialCashBalance = $cashAccount->current_balance;

        $customer = Customer::create([
            'customer_code' => 'CUST-TEST',
            'name' => 'Walk-in Client',
            'current_balance' => 0,
        ]);

        $product = Product::create([
            'product_code' => 'TEST-003',
            'name' => 'Widget B',
            'purchase_price' => 10.00,
            'sales_price' => 25.00,
            'weighted_cost' => 10.00,
            'current_stock' => 50,
        ]);

        // Sale 10 units @ AED 25 = AED 250 + 5% VAT (12.50) = 262.50
        $headerSale = [
            'invoice_number' => 'INV-000001',
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_type' => 'Cash',
            'account_id' => $cashAccount->id,
            'subtotal' => 250.00,
            'vat_amount' => 12.50,
            'grand_total' => 262.50,
        ];
        $itemsSale = [
            [
                'product_id' => $product->id,
                'quantity' => 10,
                'unit_price' => 25.00,
                'vat_percent' => 5,
                'vat_amount' => 12.50,
                'line_total' => 262.50,
            ]
        ];

        $sale = $salesService->createSale($headerSale, $itemsSale);

        $product->refresh();
        $cashAccount->refresh();

        // Stock decreased by 10 -> 40 remaining
        $this->assertEquals(40.00, $product->current_stock);
        // COGS recorded = 10 * 10.00 = AED 100.00
        $this->assertEquals(100.00, $sale->cogs_total);
        // Cash increased by AED 262.50
        $this->assertEquals($initialCashBalance + 262.50, $cashAccount->current_balance);
    }

    public function test_credit_sale_and_customer_payment(): void
    {
        $salesService = app(SalesService::class);
        $paymentService = app(PaymentService::class);
        $cashAccount = Account::where('type', 'Cash')->first();

        $customer = Customer::create([
            'customer_code' => 'CUST-CREDIT',
            'name' => 'Credit Company LLC',
            'current_balance' => 0,
        ]);

        $product = Product::create([
            'product_code' => 'TEST-004',
            'name' => 'Widget C',
            'purchase_price' => 50.00,
            'sales_price' => 100.00,
            'weighted_cost' => 50.00,
            'current_stock' => 20,
        ]);

        // Credit Sale AED 500
        $headerSale = [
            'invoice_number' => 'INV-000002',
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_type' => 'Credit',
            'subtotal' => 500.00,
            'vat_amount' => 25.00,
            'grand_total' => 525.00,
        ];
        $itemsSale = [
            [
                'product_id' => $product->id,
                'quantity' => 5,
                'unit_price' => 100.00,
                'vat_percent' => 5,
                'vat_amount' => 25.00,
                'line_total' => 525.00,
            ]
        ];

        $sale = $salesService->createSale($headerSale, $itemsSale);
        $customer->refresh();
        $this->assertEquals(525.00, $customer->current_balance);

        // Receive payment of AED 525.00 allocated to sale
        $paymentService->recordCustomerPayment(
            $customer->id,
            $cashAccount->id,
            525.00,
            now()->toDateString(),
            'REC-000001',
            'CHQ-12345',
            'Full payment',
            [$sale->id => 525.00]
        );

        $customer->refresh();
        $sale->refresh();
        // Receivable balance is now 0
        $this->assertEquals(0.00, $customer->current_balance);
        $this->assertEquals(0.00, $sale->due_amount);
        $this->assertEquals(525.00, $sale->paid_amount);
    }

    public function test_invoice_pdf_generation_displays_logo(): void
    {
        $user = User::first();
        $company = CompanySetting::first();

        $logoDirPath = storage_path('app/public/logos');
        if (!file_exists($logoDirPath)) {
            mkdir($logoDirPath, 0777, true);
        }
        $testImagePath = $logoDirPath . '/test_invoice_logo.png';
        file_put_contents($testImagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));

        $company->update(['invoice_logo' => 'logos/test_invoice_logo.png']);

        $customer = Customer::create([
            'customer_code' => 'CUST-PDF',
            'name' => 'PDF Test Client',
            'current_balance' => 0,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-PDF-999',
            'sale_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'payment_type' => 'Cash',
            'subtotal' => 100.00,
            'vat_amount' => 5.00,
            'grand_total' => 105.00,
            'paid_amount' => 105.00,
            'due_amount' => 0.00,
            'status' => 'Confirmed',
        ]);

        $this->assertNotNull($company->invoice_logo_src);
        $this->assertStringStartsWith('data:image/', $company->invoice_logo_src);

        $response = $this->actingAs($user)->get(route('sales.pdf', ['id' => $sale->id]));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'application/pdf'));

        if (file_exists($testImagePath)) {
            unlink($testImagePath);
        }
    }

    public function test_pwa_manifest_service_worker_and_offline_page_exist(): void
    {
        $this->assertFileExists(public_path('manifest.json'));
        $this->assertFileExists(public_path('sw.js'));
        $this->assertFileExists(public_path('offline.html'));
        $this->assertFileExists(public_path('assets/images/icons/icon-192x192.png'));
        $this->assertFileExists(public_path('assets/images/icons/icon-512x512.png'));

        $manifestContent = file_get_contents(public_path('manifest.json'));
        $manifest = json_decode($manifestContent, true);

        $this->assertNotNull($manifest);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertEquals('Inventory & Accounting ERP', $manifest['name']);
        $this->assertCount(10, $manifest['icons']);

        $swContent = file_get_contents(public_path('sw.js'));
        $this->assertStringContainsString('CACHE_NAME', $swContent);
        $this->assertStringContainsString('offline.html', $swContent);
    }

    public function test_yearly_invoice_numbering_sequence_and_new_year_reset(): void
    {
        $num2026_1 = \App\Models\InvoiceSetting::getNextSalesInvoiceNumber('2026-03-01');
        $this->assertMatchesRegularExpression('/^INV-2026-\d{4}$/', $num2026_1);

        // Create sale with this invoice number in 2026
        $customer = Customer::create(['customer_code' => 'CUST-YEAR', 'name' => 'Year Client']);
        Sale::create([
            'invoice_number' => $num2026_1,
            'sale_date' => '2026-03-01',
            'customer_id' => $customer->id,
            'payment_type' => 'Credit',
            'subtotal' => 100,
            'grand_total' => 105,
            'status' => 'Confirmed',
        ]);

        $num2026_2 = \App\Models\InvoiceSetting::getNextSalesInvoiceNumber('2026-03-02');
        $this->assertNotEquals($num2026_1, $num2026_2);
        $this->assertStringStartsWith('INV-2026-', $num2026_2);

        // Now request number for year 2027 -> Should start sequence afresh for 2027
        $num2027 = \App\Models\InvoiceSetting::getNextSalesInvoiceNumber('2027-01-10');
        $this->assertEquals('INV-2027-0001', $num2027);

        // Check Purchase Numbering for 2026 and 2027
        $pur2026 = \App\Models\InvoiceSetting::getNextPurchaseNumber('2026-05-15');
        $this->assertEquals('PUR-2026-0001', $pur2026);
        $pur2027 = \App\Models\InvoiceSetting::getNextPurchaseNumber('2027-02-20');
        $this->assertEquals('PUR-2027-0001', $pur2027);
    }

    public function test_purchase_and_sales_returns_with_stock_and_balance_reversals(): void
    {
        $purchaseService = app(PurchaseService::class);
        $returnService = app(\App\Services\ReturnService::class);
        $salesService = app(SalesService::class);

        $supplier = Supplier::create(['supplier_code' => 'SUP-RET', 'name' => 'Supplier Ret', 'current_balance' => 0]);
        $customer = Customer::create(['customer_code' => 'CUST-RET', 'name' => 'Customer Ret', 'current_balance' => 0]);
        $product = Product::create([
            'product_code' => 'PROD-RET',
            'name' => 'Returnable Item',
            'purchase_price' => 50,
            'sales_price' => 100,
            'weighted_cost' => 50,
            'current_stock' => 0,
        ]);

        // 1. Purchase 100 items on credit @ 50 = 5000 + 250 vat = 5250
        $purchase = $purchaseService->createPurchase([
            'purchase_number' => 'PUR-2026-0099',
            'purchase_date' => '2026-04-01',
            'supplier_id' => $supplier->id,
            'payment_type' => 'Credit',
            'subtotal' => 5000,
            'vat_amount' => 250,
            'grand_total' => 5250,
        ], [[
            'product_id' => $product->id,
            'quantity' => 100,
            'unit_price' => 50,
            'vat_percent' => 5,
            'vat_amount' => 250,
            'line_total' => 5250,
        ]]);

        $product->refresh();
        $supplier->refresh();
        $this->assertEquals(100, $product->current_stock);
        $this->assertEquals(5250, $supplier->current_balance);

        // 2. Return 20 items to supplier (Debit Note)
        $returnService->processPurchaseReturn([
            'return_number' => 'PR-2026-0001',
            'return_date' => '2026-04-05',
            'supplier_id' => $supplier->id,
            'purchase_id' => $purchase->id,
            'subtotal' => 1000,
            'vat_amount' => 50,
            'grand_total' => 1050,
            'return_reason' => 'Defective batch',
        ], [[
            'product_id' => $product->id,
            'quantity' => 20,
            'unit_price' => 50,
            'vat_percent' => 5,
            'vat_amount' => 50,
            'line_total' => 1050,
        ]]);

        $product->refresh();
        $supplier->refresh();
        // Stock decreased: 100 - 20 = 80
        $this->assertEquals(80, $product->current_stock);
        // Supplier balance decreased: 5250 - 1050 = 4200
        $this->assertEquals(4200, $supplier->current_balance);

        // 3. Sell 40 items to customer on credit @ 100 = 4000 + 200 vat = 4200
        $sale = $salesService->createSale([
            'invoice_number' => 'INV-2026-0099',
            'sale_date' => '2026-04-10',
            'customer_id' => $customer->id,
            'payment_type' => 'Credit',
            'subtotal' => 4000,
            'vat_amount' => 200,
            'grand_total' => 4200,
        ], [[
            'product_id' => $product->id,
            'quantity' => 40,
            'unit_price' => 100,
            'vat_percent' => 5,
            'vat_amount' => 200,
            'line_total' => 4200,
        ]]);

        $product->refresh();
        $customer->refresh();
        // Stock: 80 - 40 = 40
        $this->assertEquals(40, $product->current_stock);
        $this->assertEquals(4200, $customer->current_balance);

        // 4. Customer returns 10 items (Credit Note)
        $returnService->processSalesReturn([
            'return_number' => 'SR-2026-0001',
            'return_date' => '2026-04-12',
            'customer_id' => $customer->id,
            'sale_id' => $sale->id,
            'subtotal' => 1000,
            'vat_amount' => 50,
            'grand_total' => 1050,
            'return_reason' => 'Customer changed order',
        ], [[
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'vat_percent' => 5,
            'vat_amount' => 50,
            'line_total' => 1050,
        ]]);

        $product->refresh();
        $customer->refresh();
        // Stock restored: 40 + 10 = 50
        $this->assertEquals(50, $product->current_stock);
        // Customer balance reduced: 4200 - 1050 = 3150
        $this->assertEquals(3150, $customer->current_balance);
    }

    public function test_profit_and_loss_report_breakdown(): void
    {
        $reportService = app(\App\Services\ReportService::class);
        $startDate = '2026-06-01';
        $endDate = '2026-06-30';

        $customer = Customer::create(['customer_code' => 'CUST-PL', 'name' => 'PL Client']);
        $product = Product::create([
            'product_code' => 'PROD-PL',
            'name' => 'PL Item',
            'purchase_price' => 50,
            'sales_price' => 100,
            'weighted_cost' => 50,
            'current_stock' => 100,
        ]);

        // Create sale of 1000 AED
        Sale::create([
            'invoice_number' => 'INV-2026-PL01',
            'sale_date' => '2026-06-15',
            'customer_id' => $customer->id,
            'payment_type' => 'Credit',
            'subtotal' => 1000,
            'vat_amount' => 50,
            'grand_total' => 1050,
            'cogs_total' => 500,
            'status' => 'Confirmed',
        ]);

        // Create expense of 200 AED
        $expCat = \App\Models\ExpenseCategory::create(['name' => 'Utilities']);
        $cashAcc = Account::first();
        \App\Models\Expense::create([
            'date' => '2026-06-16',
            'expense_category_id' => $expCat->id,
            'account_id' => $cashAcc->id,
            'amount' => 200,
            'description' => 'Electricity Bill',
        ]);

        // Create other income of 150 AED
        $incCat = \App\Models\IncomeCategory::create(['name' => 'Consulting']);
        \App\Models\Income::create([
            'date' => '2026-06-18',
            'income_category_id' => $incCat->id,
            'account_id' => $cashAcc->id,
            'amount' => 150,
            'description' => 'Project Consulting',
        ]);

        $report = $reportService->getProfitLossReport($startDate, $endDate);

        $this->assertEquals(1050, $report['revenue']['gross_sales']);
        $this->assertEquals(1050, $report['revenue']['net_sales']);
        $this->assertEquals(150, $report['other_income']);
        $this->assertArrayHasKey('Consulting', $report['other_income_breakdown']);
        $this->assertEquals(200, $report['expenses']);
        $this->assertArrayHasKey('Utilities', $report['expenses_breakdown']);
        $this->assertArrayHasKey('net_profit', $report);
    }

    public function test_daily_report_metrics(): void
    {
        $reportService = app(\App\Services\ReportService::class);
        $today = now()->toDateString();

        $daily = $reportService->getDailyReport($today);

        $this->assertArrayHasKey('sales', $daily);
        $this->assertArrayHasKey('purchases', $daily);
        $this->assertArrayHasKey('income', $daily);
        $this->assertArrayHasKey('expense', $daily);
        $this->assertArrayHasKey('cash_balance', $daily);
        $this->assertArrayHasKey('bank_balance', $daily);
    }

    public function test_account_transfer_and_ledger(): void
    {
        $accountingService = app(\App\Services\AccountingService::class);
        $cashAccount = Account::where('type', 'Cash')->first();
        $bankAccount = Account::where('type', 'Bank')->first();

        $initialCash = $cashAccount->current_balance;
        $initialBank = $bankAccount->current_balance;

        $accountingService->transfer(
            $cashAccount->id,
            $bankAccount->id,
            300.00,
            now()->toDateString(),
            'Cash deposit into bank'
        );

        $cashAccount->refresh();
        $bankAccount->refresh();

        $this->assertEquals($initialCash - 300.00, $cashAccount->current_balance);
        $this->assertEquals($initialBank + 300.00, $bankAccount->current_balance);
    }

    public function test_out_of_stock_sale_prevention(): void
    {
        $stockService = app(StockService::class);
        $product = Product::create([
            'product_code' => 'PROD-EMPTY',
            'name' => 'Empty Stock Item',
            'purchase_price' => 20,
            'sales_price' => 40,
            'weighted_cost' => 20,
            'current_stock' => 0,
        ]);

        \App\Models\GeneralSetting::updateOrCreate(['id' => 1], ['allow_negative_stock' => false]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        $stockService->recordMovement(
            $product->id,
            now()->toDateString(),
            'SALE',
            0,
            5,
            20,
            null,
            null,
            'Attempted sale with zero stock'
        );
    }
}
