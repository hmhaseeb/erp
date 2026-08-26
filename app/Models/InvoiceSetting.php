<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Generic engine to generate yearly sequential document numbers.
     * Format: [PREFIX]-[YEAR]-[0001]
     */
    public static function getNextDocumentNumber(
        string $modelClass,
        string $column,
        string $defaultPrefix,
        ?string $date = null,
        ?string $customPrefix = null,
        mixed $customStart = null,
        string $dateColumn = 'created_at'
    ): string {
        $cleanPrefix = rtrim($customPrefix ?? $defaultPrefix, '-');
        if (empty($cleanPrefix)) {
            $cleanPrefix = rtrim($defaultPrefix, '-');
        }

        $startNum = $customStart !== null && (int)$customStart > 0 ? (int)$customStart : 1;

        $year = $date ? date('Y', strtotime($date)) : date('Y');
        $yearPattern = "{$cleanPrefix}-{$year}-%";

        // Query only records matching this prefix and year sequence
        $existingNumbers = $modelClass::where($column, 'like', $yearPattern)->pluck($column);

        $maxSeq = 0;
        foreach ($existingNumbers as $num) {
            if (preg_match('/' . preg_quote($cleanPrefix . '-' . $year . '-', '/') . '(\d+)/', $num, $matches)) {
                $seq = (int)$matches[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        $nextSeq = max($startNum, $maxSeq + 1);
        $candidate = "{$cleanPrefix}-{$year}-" . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

        while ($modelClass::where($column, $candidate)->exists()) {
            $nextSeq++;
            $candidate = "{$cleanPrefix}-{$year}-" . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public static function getNextSalesInvoiceNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->invoice_prefix ?? 'INV-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(Sale::class, 'invoice_number', 'INV-', $date, $prefix, $start, 'sale_date');
    }

    public static function getNextPurchaseNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->purchase_prefix ?? 'PUR-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(Purchase::class, 'purchase_number', 'PUR-', $date, $prefix, $start, 'purchase_date');
    }

    public static function getNextSalesReturnNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->sales_return_prefix ?? 'SR-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(SalesReturn::class, 'return_number', 'SR-', $date, $prefix, $start, 'return_date');
    }

    public static function getNextPurchaseReturnNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->purchase_return_prefix ?? 'PR-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(PurchaseReturn::class, 'return_number', 'PR-', $date, $prefix, $start, 'return_date');
    }

    public static function getNextCustomerPaymentNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->customer_payment_prefix ?? 'REC-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(CustomerPayment::class, 'payment_number', 'REC-', $date, $prefix, $start, 'payment_date');
    }

    public static function getNextSupplierPaymentNumber($date = null, $customPrefix = null, $customStart = null): string
    {
        $setting = self::first();
        $prefix = $customPrefix ?? ($setting->supplier_payment_prefix ?? 'PAY-');
        $start = $customStart !== null ? (int)$customStart : ($setting ? (int)$setting->starting_number : 1);
        return self::getNextDocumentNumber(SupplierPayment::class, 'payment_number', 'PAY-', $date, $prefix, $start, 'payment_date');
    }
}
