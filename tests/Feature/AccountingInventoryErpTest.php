<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
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
}
