<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\CustomerPaymentAllocation;
use App\Models\CustomerTransaction;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierPaymentAllocation;
use App\Models\SupplierTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    /**
     * Record customer payment and allocate to sales invoices.
     */
    public function recordCustomerPayment(
        int $customerId,
        int $accountId,
        float $amount,
        string $date,
        ?string $paymentNumber = null,
        ?string $referenceNumber = null,
        ?string $notes = null,
        array $allocations = []
    ): CustomerPayment {
        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        return DB::transaction(function () use (
            $customerId, $accountId, $amount, $date, $paymentNumber, $referenceNumber, $notes, $allocations
        ) {
            $customer = Customer::findOrFail($customerId);

            if (!$paymentNumber) {
                $setting = \App\Models\InvoiceSetting::first();
                $prefix = $setting ? $setting->customer_payment_prefix : 'REC-';
                $paymentNumber = $prefix . str_pad((string)(CustomerPayment::max('id') + 1), 6, '0', STR_PAD_LEFT);
            }

            $payment = CustomerPayment::create([
                'payment_number' => $paymentNumber,
                'payment_date' => $date,
                'customer_id' => $customer->id,
                'account_id' => $accountId,
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ]);

            // Account Debit (Inflow)
            $this->accountingService->recordTransaction(
                $accountId,
                $date,
                'Customer Payment',
                $amount,
                0,
                CustomerPayment::class,
                $payment->id,
                "Customer Payment #{$payment->payment_number} from {$customer->name}"
            );

            // Customer Receivable decreases (Credit entry)
            $newBalance = $customer->current_balance - $amount;
            CustomerTransaction::create([
                'customer_id' => $customer->id,
                'date' => $date,
                'transaction_type' => 'PAYMENT',
                'reference_type' => CustomerPayment::class,
                'reference_id' => $payment->id,
                'debit' => 0,
                'credit' => $amount,
                'balance' => $newBalance,
                'description' => "Payment Receipt #{$payment->payment_number}",
            ]);
            $customer->update(['current_balance' => $newBalance]);

            // Process allocations
            foreach ($allocations as $saleId => $allocatedAmt) {
                if ($allocatedAmt > 0) {
                    CustomerPaymentAllocation::create([
                        'customer_payment_id' => $payment->id,
                        'sale_id' => $saleId,
                        'allocated_amount' => $allocatedAmt,
                    ]);

                    $sale = Sale::findOrFail($saleId);
                    $newPaid = $sale->paid_amount + $allocatedAmt;
                    $newDue = max(0, $sale->grand_total - $newPaid);
                    $sale->update([
                        'paid_amount' => $newPaid,
                        'due_amount' => $newDue,
                    ]);
                }
            }

            return $payment;
        });
    }

    /**
     * Record supplier payment and allocate to purchase invoices.
     */
    public function recordSupplierPayment(
        int $supplierId,
        int $accountId,
        float $amount,
        string $date,
        ?string $paymentNumber = null,
        ?string $referenceNumber = null,
        ?string $notes = null,
        array $allocations = []
    ): SupplierPayment {
        if ($amount <= 0) {
            throw new Exception("Payment amount must be greater than zero.");
        }

        return DB::transaction(function () use (
            $supplierId, $accountId, $amount, $date, $paymentNumber, $referenceNumber, $notes, $allocations
        ) {
            $supplier = Supplier::findOrFail($supplierId);

            if (!$paymentNumber) {
                $setting = \App\Models\InvoiceSetting::first();
                $prefix = $setting ? $setting->supplier_payment_prefix : 'PAY-';
                $paymentNumber = $prefix . str_pad((string)(SupplierPayment::max('id') + 1), 6, '0', STR_PAD_LEFT);
            }

            $payment = SupplierPayment::create([
                'payment_number' => $paymentNumber,
                'payment_date' => $date,
                'supplier_id' => $supplier->id,
                'account_id' => $accountId,
                'amount' => $amount,
                'reference_number' => $referenceNumber,
                'notes' => $notes,
            ]);

            // Account Credit (Outflow)
            $this->accountingService->recordTransaction(
                $accountId,
                $date,
                'Supplier Payment',
                0,
                $amount,
                SupplierPayment::class,
                $payment->id,
                "Supplier Payment #{$payment->payment_number} to {$supplier->name}"
            );

            // Supplier Payable decreases (Debit entry)
            $newBalance = $supplier->current_balance - $amount;
            SupplierTransaction::create([
                'supplier_id' => $supplier->id,
                'date' => $date,
                'transaction_type' => 'PAYMENT',
                'reference_type' => SupplierPayment::class,
                'reference_id' => $payment->id,
                'debit' => $amount,
                'credit' => 0,
                'balance' => $newBalance,
                'description' => "Payment Voucher #{$payment->payment_number}",
            ]);
            $supplier->update(['current_balance' => $newBalance]);

            // Process allocations
            foreach ($allocations as $purchaseId => $allocatedAmt) {
                if ($allocatedAmt > 0) {
                    SupplierPaymentAllocation::create([
                        'supplier_payment_id' => $payment->id,
                        'purchase_id' => $purchaseId,
                        'allocated_amount' => $allocatedAmt,
                    ]);

                    $purchase = Purchase::findOrFail($purchaseId);
                    $newPaid = $purchase->paid_amount + $allocatedAmt;
                    $newDue = max(0, $purchase->grand_total - $newPaid);
                    $purchase->update([
                        'paid_amount' => $newPaid,
                        'due_amount' => $newDue,
                    ]);
                }
            }

            return $payment;
        });
    }
}
