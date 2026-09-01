<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesService
{
    protected StockService $stockService;
    protected AccountingService $accountingService;

    public function __construct(StockService $stockService, AccountingService $accountingService)
    {
        $this->stockService = $stockService;
        $this->accountingService = $accountingService;
    }

    /**
     * Create and confirm a sales invoice inside DB transaction with pessimistic locking.
     */
    public function createSale(array $header, array $items): Sale
    {
        return DB::transaction(function () use ($header, $items) {
            $generalSetting = \App\Models\GeneralSetting::first();
            $allowNegativeStock = $generalSetting ? (bool)$generalSetting->allow_negative_stock : false;

            // 1. Aggregate requested quantities per product
            $productQuantities = [];
            foreach ($items as $item) {
                $pid = $item['product_id'];
                $qty = (float)($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    throw new Exception("Invalid quantity for product ID #{$pid}. Quantity must be greater than zero.");
                }
                $productQuantities[$pid] = ($productQuantities[$pid] ?? 0) + $qty;
            }

            // 2. Lock products for update to prevent concurrent race conditions
            $products = Product::whereIn('id', array_keys($productQuantities))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // 3. Strict stock validation against real-time database state
            foreach ($productQuantities as $pid => $totalRequestedQty) {
                if (!isset($products[$pid])) {
                    throw new Exception("Selected product (ID: {$pid}) does not exist in catalog.");
                }

                $prod = $products[$pid];
                if (!$prod->status) {
                    throw new Exception("Product '{$prod->name}' is marked inactive and cannot be sold.");
                }

                $availStock = (float)$prod->current_stock;
                if (!$allowNegativeStock) {
                    if ($availStock <= 0) {
                        throw new Exception("Product '{$prod->name}' is currently out of stock (0 available).");
                    }
                    if ($totalRequestedQty > $availStock) {
                        throw new Exception("Insufficient stock for '{$prod->name}'. Available stock is {$availStock}, requested {$totalRequestedQty}.");
                    }
                }
            }

            // 4. Calculate COGS & prepare item data
            $cogsTotal = 0.00;
            $processedItems = [];

            foreach ($items as $item) {
                $product = $products[$item['product_id']];
                $unitCost = (float) $product->weighted_cost;
                $qty = (float) $item['quantity'];
                $cogsAmount = $qty * $unitCost;
                $cogsTotal += $cogsAmount;

                $processedItems[] = array_merge($item, [
                    'unit_cost' => $unitCost,
                    'cogs_amount' => $cogsAmount,
                ]);
            }

            // 5. Unique invoice number check
            $invNum = $header['invoice_number'];
            if (Sale::where('invoice_number', $invNum)->exists()) {
                $invNum = \App\Models\InvoiceSetting::getNextSalesInvoiceNumber($header['sale_date'] ?? null);
            }

            // 6. Create Sale record
            $sale = Sale::create([
                'invoice_number' => $invNum,
                'sale_date' => $header['sale_date'],
                'customer_id' => $header['customer_id'],
                'payment_type' => $header['payment_type'],
                'account_id' => $header['account_id'] ?? null,
                'subtotal' => $header['subtotal'],
                'discount_amount' => $header['discount_amount'] ?? 0,
                'vat_amount' => $header['vat_amount'] ?? 0,
                'grand_total' => $header['grand_total'],
                'paid_amount' => $header['payment_type'] !== 'Credit' ? $header['grand_total'] : 0,
                'due_amount' => $header['payment_type'] === 'Credit' ? $header['grand_total'] : 0,
                'cogs_total' => $cogsTotal,
                'status' => 'Confirmed',
                'notes' => $header['notes'] ?? null,
            ]);

            // 7. Create Sale Items and deduct stock
            foreach ($processedItems as $itemData) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'discount_amount' => $itemData['discount_amount'] ?? 0,
                    'vat_percent' => $itemData['vat_percent'] ?? 0,
                    'vat_amount' => $itemData['vat_amount'] ?? 0,
                    'line_total' => $itemData['line_total'],
                    'cogs_amount' => $itemData['cogs_amount'],
                ]);

                // Reduce stock via StockService
                $this->stockService->recordMovement(
                    $itemData['product_id'],
                    $sale->sale_date,
                    'SALE',
                    0,
                    $itemData['quantity'],
                    $itemData['unit_cost'],
                    Sale::class,
                    $sale->id,
                    "Sales Invoice #{$sale->invoice_number}"
                );
            }

            // 8. Financial ledger & payment accounting
            $customer = Customer::findOrFail($sale->customer_id);

            if ($sale->payment_type === 'Credit') {
                // Customer Receivable increases (Debit entry on customer ledger)
                $newCustomerBalance = $customer->current_balance + $sale->grand_total;
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => $sale->sale_date,
                    'transaction_type' => 'SALE',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'debit' => $sale->grand_total,
                    'credit' => 0,
                    'balance' => $newCustomerBalance,
                    'description' => "Sales Invoice #{$sale->invoice_number}",
                ]);
                $customer->update(['current_balance' => $newCustomerBalance]);
            } else {
                // Immediate Cash/Bank Payment (Account Debit / Inflow)
                if (!$sale->account_id) {
                    throw new Exception("Account must be selected for Cash/Bank sales.");
                }
                $this->accountingService->recordTransaction(
                    $sale->account_id,
                    $sale->sale_date,
                    'Sales Receipt',
                    $sale->grand_total,
                    0,
                    Sale::class,
                    $sale->id,
                    "Payment received for Sales Invoice #{$sale->invoice_number}"
                );

                // Record customer ledger showing sale and immediate payment
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => $sale->sale_date,
                    'transaction_type' => 'SALE',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'debit' => $sale->grand_total,
                    'credit' => $sale->grand_total,
                    'balance' => $customer->current_balance,
                    'description' => "Direct {$sale->payment_type} Sales Invoice #{$sale->invoice_number}",
                ]);
            }

            return $sale;
        });
    }

    /**
     * Cancel sale and safely restore stock & reverse financial entries.
     */
    public function cancelSale(int $saleId): void
    {
        DB::transaction(function () use ($saleId) {
            $sale = Sale::with('items')->findOrFail($saleId);

            if ($sale->status === 'Cancelled') {
                throw new Exception("Sale is already cancelled.");
            }

            // Restore stock
            foreach ($sale->items as $item) {
                $this->stockService->recordMovement(
                    $item->product_id,
                    now()->toDateString(),
                    'ADJUSTMENT_IN',
                    $item->quantity,
                    0,
                    $item->unit_cost,
                    Sale::class,
                    $sale->id,
                    "Reversal for cancelled sale #{$sale->invoice_number}"
                );
            }

            $customer = Customer::findOrFail($sale->customer_id);

            if ($sale->payment_type === 'Credit') {
                $newBalance = $customer->current_balance - $sale->due_amount;
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => now()->toDateString(),
                    'transaction_type' => 'ADJUSTMENT',
                    'reference_type' => Sale::class,
                    'reference_id' => $sale->id,
                    'debit' => 0,
                    'credit' => $sale->due_amount,
                    'balance' => $newBalance,
                    'description' => "Cancellation of Sales Invoice #{$sale->invoice_number}",
                ]);
                $customer->update(['current_balance' => $newBalance]);
            } else if ($sale->account_id) {
                // Outflow / Refund from account
                $this->accountingService->recordTransaction(
                    $sale->account_id,
                    now()->toDateString(),
                    'Sale Reversal',
                    0,
                    $sale->paid_amount,
                    Sale::class,
                    $sale->id,
                    "Reversal of Sales Receipt #{$sale->invoice_number}"
                );
            }

            $sale->update(['status' => 'Cancelled']);
        });
    }
}
