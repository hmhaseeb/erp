<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseService
{
    protected StockService $stockService;
    protected AccountingService $accountingService;

    public function __construct(StockService $stockService, AccountingService $accountingService)
    {
        $this->stockService = $stockService;
        $this->accountingService = $accountingService;
    }

    /**
     * Create and confirm a purchase invoice inside DB transaction.
     */
    public function createPurchase(array $header, array $items): Purchase
    {
        return DB::transaction(function () use ($header, $items) {
            $purchaseNum = $header['purchase_number'] ?? null;
            if (empty($purchaseNum) || Purchase::where('purchase_number', $purchaseNum)->exists()) {
                $purchaseNum = \App\Models\InvoiceSetting::getNextPurchaseNumber($header['purchase_date'] ?? null);
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNum,
                'purchase_date' => $header['purchase_date'],
                'supplier_id' => $header['supplier_id'],
                'reference_number' => $header['reference_number'] ?? null,
                'payment_type' => $header['payment_type'],
                'account_id' => $header['account_id'] ?? null,
                'subtotal' => $header['subtotal'],
                'discount_amount' => $header['discount_amount'] ?? 0,
                'vat_amount' => $header['vat_amount'] ?? 0,
                'grand_total' => $header['grand_total'],
                'paid_amount' => $header['payment_type'] !== 'Credit' ? $header['grand_total'] : 0,
                'due_amount' => $header['payment_type'] === 'Credit' ? $header['grand_total'] : 0,
                'status' => 'Confirmed',
                'notes' => $header['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'vat_percent' => $item['vat_percent'] ?? 0,
                    'vat_amount' => $item['vat_amount'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);

                // Stock In + Recalculate Weighted Average Cost
                $this->stockService->recordMovement(
                    $item['product_id'],
                    $purchase->purchase_date,
                    'PURCHASE',
                    $item['quantity'],
                    0,
                    $item['unit_price'],
                    Purchase::class,
                    $purchase->id,
                    "Purchase Invoice #{$purchase->purchase_number}"
                );
            }

            $supplier = Supplier::findOrFail($purchase->supplier_id);

            if ($purchase->payment_type === 'Credit') {
                // Supplier Payable increases (Credit entry on supplier ledger)
                $newSupplierBalance = $supplier->current_balance + $purchase->grand_total;
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $purchase->purchase_date,
                    'transaction_type' => 'PURCHASE',
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'debit' => 0,
                    'credit' => $purchase->grand_total,
                    'balance' => $newSupplierBalance,
                    'description' => "Purchase Invoice #{$purchase->purchase_number}",
                ]);
                $supplier->update(['current_balance' => $newSupplierBalance]);
            } else {
                // Immediate Cash/Bank Payment (Account Credit / Outflow)
                if (!$purchase->account_id) {
                    throw new Exception("Account must be selected for Cash/Bank purchases.");
                }
                $this->accountingService->recordTransaction(
                    $purchase->account_id,
                    $purchase->purchase_date,
                    'Purchase Payment',
                    0,
                    $purchase->grand_total,
                    Purchase::class,
                    $purchase->id,
                    "Payment for Purchase #{$purchase->purchase_number}"
                );

                // Also record supplier transaction showing purchase & full payment
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => $purchase->purchase_date,
                    'transaction_type' => 'PURCHASE',
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'debit' => $purchase->grand_total,
                    'credit' => $purchase->grand_total,
                    'balance' => $supplier->current_balance,
                    'description' => "Direct {$purchase->payment_type} Purchase Invoice #{$purchase->purchase_number}",
                ]);
            }

            return $purchase;
        });
    }

    /**
     * Cancel purchase and safely reverse stock & financial entries.
     */
    public function cancelPurchase(int $purchaseId): void
    {
        DB::transaction(function () use ($purchaseId) {
            $purchase = Purchase::with('items')->findOrFail($purchaseId);

            if ($purchase->status === 'Cancelled') {
                throw new Exception("Purchase is already cancelled.");
            }

            // Reverse stock
            foreach ($purchase->items as $item) {
                $this->stockService->recordMovement(
                    $item->product_id,
                    now()->toDateString(),
                    'ADJUSTMENT_OUT',
                    0,
                    $item->quantity,
                    $item->unit_price,
                    Purchase::class,
                    $purchase->id,
                    "Reversal for cancelled purchase #{$purchase->purchase_number}"
                );
            }

            $supplier = Supplier::findOrFail($purchase->supplier_id);

            if ($purchase->payment_type === 'Credit') {
                $newBalance = $supplier->current_balance - $purchase->due_amount;
                SupplierTransaction::create([
                    'supplier_id' => $supplier->id,
                    'date' => now()->toDateString(),
                    'transaction_type' => 'ADJUSTMENT',
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'debit' => $purchase->due_amount,
                    'credit' => 0,
                    'balance' => $newBalance,
                    'description' => "Cancellation of Purchase #{$purchase->purchase_number}",
                ]);
                $supplier->update(['current_balance' => $newBalance]);
            } else if ($purchase->account_id) {
                // Refund account
                $this->accountingService->recordTransaction(
                    $purchase->account_id,
                    now()->toDateString(),
                    'Purchase Reversal',
                    $purchase->paid_amount,
                    0,
                    Purchase::class,
                    $purchase->id,
                    "Reversal of Purchase Payment #{$purchase->purchase_number}"
                );
            }

            $purchase->update(['status' => 'Cancelled']);
        });
    }
}
