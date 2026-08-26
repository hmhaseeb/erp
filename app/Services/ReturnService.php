<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Sale;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class ReturnService
{
    protected StockService $stockService;
    protected AccountingService $accountingService;

    public function __construct(StockService $stockService, AccountingService $accountingService)
    {
        $this->stockService = $stockService;
        $this->accountingService = $accountingService;
    }

    /**
     * Process Sales Return (Restores Stock & Adjusts Balance/Refund)
     */
    public function processSalesReturn(array $header, array $items): SalesReturn
    {
        return DB::transaction(function () use ($header, $items) {
            $cogsTotal = 0;
            $processedItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unitCost = (float) $product->weighted_cost;
                $cogsAmount = $item['quantity'] * $unitCost;
                $cogsTotal += $cogsAmount;

                $processedItems[] = array_merge($item, [
                    'unit_cost' => $unitCost,
                    'cogs_amount' => $cogsAmount,
                ]);
            }

            $returnNum = $header['return_number'] ?? null;
            if (empty($returnNum) || SalesReturn::where('return_number', $returnNum)->exists()) {
                $returnNum = \App\Models\InvoiceSetting::getNextSalesReturnNumber($header['return_date'] ?? null);
            }

            $salesReturn = SalesReturn::create([
                'return_number' => $returnNum,
                'return_date' => $header['return_date'],
                'customer_id' => $header['customer_id'],
                'sale_id' => $header['sale_id'] ?? null,
                'account_id' => $header['account_id'] ?? null,
                'subtotal' => $header['subtotal'],
                'vat_amount' => $header['vat_amount'] ?? 0,
                'grand_total' => $header['grand_total'],
                'cogs_total' => $cogsTotal,
                'return_reason' => $header['return_reason'] ?? null,
                'status' => 'Confirmed',
            ]);

            foreach ($processedItems as $itemData) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'sale_item_id' => $itemData['sale_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'unit_cost' => $itemData['unit_cost'],
                    'vat_percent' => $itemData['vat_percent'] ?? 0,
                    'vat_amount' => $itemData['vat_amount'] ?? 0,
                    'line_total' => $itemData['line_total'],
                    'cogs_amount' => $itemData['cogs_amount'],
                ]);

                // Stock In (Return to inventory)
                $this->stockService->recordMovement(
                    $itemData['product_id'],
                    $salesReturn->return_date,
                    'SALES_RETURN',
                    $itemData['quantity'],
                    0,
                    $itemData['unit_cost'],
                    SalesReturn::class,
                    $salesReturn->id,
                    "Sales Return #{$salesReturn->return_number}"
                );
            }

            $customer = Customer::findOrFail($salesReturn->customer_id);

            if (!empty($header['account_id'])) {
                // Immediate Cash/Bank Refund to Customer (Outflow / Credit)
                $this->accountingService->recordTransaction(
                    $header['account_id'],
                    $salesReturn->return_date,
                    'Sales Refund',
                    0,
                    $salesReturn->grand_total,
                    SalesReturn::class,
                    $salesReturn->id,
                    "Cash Refund for Sales Return #{$salesReturn->return_number}"
                );

                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => $salesReturn->return_date,
                    'transaction_type' => 'RETURN',
                    'reference_type' => SalesReturn::class,
                    'reference_id' => $salesReturn->id,
                    'debit' => $salesReturn->grand_total,
                    'credit' => $salesReturn->grand_total,
                    'balance' => $customer->current_balance,
                    'description' => "Cash Refunded Sales Return #{$salesReturn->return_number}",
                ]);
            } else {
                // Credit Note: Reduces customer's receivable balance (Credit entry)
                $newBalance = $customer->current_balance - $salesReturn->grand_total;
                CustomerTransaction::create([
                    'customer_id' => $customer->id,
                    'date' => $salesReturn->return_date,
                    'transaction_type' => 'RETURN',
                    'reference_type' => SalesReturn::class,
                    'reference_id' => $salesReturn->id,
                    'debit' => 0,
                    'credit' => $salesReturn->grand_total,
                    'balance' => $newBalance,
                    'description' => "Credit Note for Sales Return #{$salesReturn->return_number}",
                ]);
                $customer->update(['current_balance' => $newBalance]);
            }

            return $salesReturn;
        });
    }

    /**
     * Process Purchase Return (Reduces Stock & Adjusts Supplier Balance/Refund)
     */
    public function processPurchaseReturn(array $header, array $items): PurchaseReturn
    {
        return DB::transaction(function () use ($header, $items) {
            $returnNum = $header['return_number'] ?? null;
            if (empty($returnNum) || PurchaseReturn::where('return_number', $returnNum)->exists()) {
                $returnNum = \App\Models\InvoiceSetting::getNextPurchaseReturnNumber($header['return_date'] ?? null);
            }

            $purchaseReturn = PurchaseReturn::create([
                'return_number' => $returnNum,
                'return_date' => $header['return_date'],
                'supplier_id' => $header['supplier_id'],
                'purchase_id' => $header['purchase_id'] ?? null,
                'account_id' => $header['account_id'] ?? null,
                'subtotal' => $header['subtotal'],
                'vat_amount' => $header['vat_amount'] ?? 0,
                'grand_total' => $header['grand_total'],
                'return_reason' => $header['return_reason'] ?? null,
                'status' => 'Confirmed',
            ]);

            foreach ($items as $itemData) {
                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'purchase_item_id' => $itemData['purchase_item_id'] ?? null,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'vat_percent' => $itemData['vat_percent'] ?? 0,
                    'vat_amount' => $itemData['vat_amount'] ?? 0,
                    'line_total' => $itemData['line_total'],
                ]);

                // Stock Out (Return to supplier)
                $this->stockService->recordMovement(
                    $itemData['product_id'],
                    $purchaseReturn->return_date,
                    'PURCHASE_RETURN',
                    0,
                    $itemData['quantity'],
                    $itemData['unit_price'],
                    PurchaseReturn::class,
                    $purchaseReturn->id,
                    "Purchase Return #{$purchaseReturn->return_number}"
                );
            }

            $supplier = Supplier::findOrFail($purchaseReturn->supplier_id);

            if (!empty($header['account_id'])) {
                // Immediate Cash/Bank Refund from Supplier (Inflow / Debit)
                $this->accountingService->recordTransaction(
                    $header['account_id'],
                    $purchaseReturn->return_date,
                    'Purchase Refund',
                    $purchaseReturn->grand_total,
                    0,
                    PurchaseReturn::class,
                    $purchaseReturn->id,
                    "Cash Refund from Purchase Return #{$purchaseReturn->return_number}"
                );

                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $purchaseReturn->return_date,
                    'transaction_type' => 'RETURN',
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $purchaseReturn->id,
                    'debit' => $purchaseReturn->grand_total,
                    'credit' => $purchaseReturn->grand_total,
                    'balance' => $supplier->current_balance,
                    'description' => "Cash Refunded Purchase Return #{$purchaseReturn->return_number}",
                ]);
            } else {
                // Debit Note: Reduces supplier's payable balance (Debit entry)
                $newBalance = $supplier->current_balance - $purchaseReturn->grand_total;
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $purchaseReturn->return_date,
                    'transaction_type' => 'RETURN',
                    'reference_type' => PurchaseReturn::class,
                    'reference_id' => $purchaseReturn->id,
                    'debit' => $purchaseReturn->grand_total,
                    'credit' => 0,
                    'balance' => $newBalance,
                    'description' => "Debit Note for Purchase Return #{$purchaseReturn->return_number}",
                ]);
                $supplier->update(['current_balance' => $newBalance]);
            }

            return $purchaseReturn;
        });
    }
}
