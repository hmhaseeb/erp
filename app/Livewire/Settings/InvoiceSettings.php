<?php

namespace App\Livewire\Settings;

use App\Models\InvoiceSetting;
use App\Models\Sale;
use App\Services\SettingsService;
use Livewire\Component;

class InvoiceSettings extends Component
{
    public $invoice_prefix = 'INV-';
    public $starting_number = 1;
    public $purchase_prefix = 'PUR-';
    public $sales_return_prefix = 'SR-';
    public $purchase_return_prefix = 'PR-';
    public $customer_payment_prefix = 'REC-';
    public $supplier_payment_prefix = 'PAY-';
    public $invoice_footer = 'Thank you for your business! We appreciate your trust and look forward to serving you again.';
    public $terms_conditions = "1. Payment is due within 30 days of invoice date.\n2. Goods remain the property of the seller until full payment is received.\n3. All disputes are subject to the jurisdiction of the local court.\n4. Returned goods are accepted only within 7 days of delivery with original packaging.";
    public $payment_terms = 'Net 30 Days';
    public $bank_details = "Account Name: Your Company Name\nBank Name: National Commercial Bank\nAccount No: 1234-5678-9012\nIBAN: SA00 0000 0000 0000 0000\nSWIFT/BIC: NCBKSAJE";
    public $paper_size = 'A4';

    protected function rules()
    {
        return [
            'invoice_prefix' => 'required|string|max:20',
            'starting_number' => 'required|integer|min:1',
            'purchase_prefix' => 'required|string|max:20',
            'sales_return_prefix' => 'nullable|string|max:20',
            'purchase_return_prefix' => 'nullable|string|max:20',
            'customer_payment_prefix' => 'nullable|string|max:20',
            'supplier_payment_prefix' => 'nullable|string|max:20',
            'paper_size' => 'required|in:A4,A5,Letter',
        ];
    }

    public function mount()
    {
        $setting = InvoiceSetting::first();
        if ($setting) {
            $this->invoice_prefix = $setting->invoice_prefix ?: 'INV-';
            $this->starting_number = $setting->starting_number ?: 1;
            $this->purchase_prefix = $setting->purchase_prefix ?: 'PUR-';
            $this->sales_return_prefix = $setting->sales_return_prefix ?: 'SR-';
            $this->purchase_return_prefix = $setting->purchase_return_prefix ?: 'PR-';
            $this->customer_payment_prefix = $setting->customer_payment_prefix ?: 'REC-';
            $this->supplier_payment_prefix = $setting->supplier_payment_prefix ?: 'PAY-';
            $this->invoice_footer = $setting->invoice_footer ?: $this->invoice_footer;
            $this->terms_conditions = $setting->terms_conditions ?: $this->terms_conditions;
            $this->payment_terms = $setting->payment_terms ?: $this->payment_terms;
            $this->bank_details = $setting->bank_details ?: $this->bank_details;
            $this->paper_size = $setting->paper_size ?: 'A4';
        }
    }

    public function saveSettings()
    {
        $this->validate();

        InvoiceSetting::updateOrCreate(
            ['id' => 1],
            [
                'invoice_prefix' => $this->invoice_prefix,
                'starting_number' => $this->starting_number,
                'purchase_prefix' => $this->purchase_prefix,
                'sales_return_prefix' => $this->sales_return_prefix,
                'purchase_return_prefix' => $this->purchase_return_prefix,
                'customer_payment_prefix' => $this->customer_payment_prefix,
                'supplier_payment_prefix' => $this->supplier_payment_prefix,
                'invoice_footer' => $this->invoice_footer,
                'terms_conditions' => $this->terms_conditions,
                'payment_terms' => $this->payment_terms,
                'bank_details' => $this->bank_details,
                'paper_size' => $this->paper_size,
            ]
        );

        SettingsService::clearCache();

        session()->flash('success', 'Invoice & numbering settings updated successfully.');
        $this->dispatch('toast', message: 'Invoice & numbering settings updated successfully.', type: 'success', title: 'Settings Saved');

        $this->dispatch('check-and-open-setup-wizard');
    }

    public function loadDefaults()
    {
        $this->invoice_prefix         = 'INV-';
        $this->purchase_prefix        = 'PUR-';
        $this->sales_return_prefix    = 'SR-';
        $this->purchase_return_prefix = 'PR-';
        $this->customer_payment_prefix = 'REC-';
        $this->supplier_payment_prefix = 'PAY-';
        $this->starting_number        = 1;
        $this->paper_size             = 'A4';
        $this->payment_terms          = 'Net 30 Days';
        $this->invoice_footer         = 'Thank you for your business! We appreciate your trust and look forward to serving you again.';
        $this->terms_conditions       = "1. Payment is due within 30 days of invoice date.\n2. Goods remain the property of the seller until full payment is received.\n3. All disputes are subject to the jurisdiction of the local court.\n4. Returned goods are accepted only within 7 days of delivery with original packaging.";
        $this->bank_details           = "Account Name: Your Company Name\nBank Name: National Commercial Bank\nAccount No: 1234-5678-9012\nIBAN: SA00 0000 0000 0000 0000\nSWIFT/BIC: NCBKSAJE";

        $this->dispatch('toast', message: 'Demo defaults loaded. Review and click Save to apply.', type: 'info', title: 'Defaults Loaded');
    }

    public function render()
    {
        $currentYear = (int) date('Y');
        $previousYear = $currentYear - 1;
        $nextYear = $currentYear + 1;

        // Current Year statistics
        $nextCurrentYearNumber = InvoiceSetting::getNextSalesInvoiceNumber(date('Y-m-d'), $this->invoice_prefix, $this->starting_number);
        $currentYearSalesCount = Sale::whereYear('sale_date', $currentYear)->count();
        $currentYearLatestSale = Sale::whereYear('sale_date', $currentYear)->latest('id')->first();
        $currentYearLastNumber = $currentYearLatestSale ? $currentYearLatestSale->invoice_number : 'None (No records yet)';

        // Previous Year statistics
        $previousYearSalesCount = Sale::whereYear('sale_date', $previousYear)->count();
        $previousYearLatestSale = Sale::whereYear('sale_date', $previousYear)->latest('id')->first();
        $previousYearLastNumber = $previousYearLatestSale ? $previousYearLatestSale->invoice_number : 'None (No records for ' . $previousYear . ')';

        // Live next document numbers for current year
        $nextPurchaseNumber = InvoiceSetting::getNextPurchaseNumber(date('Y-m-d'), $this->purchase_prefix, $this->starting_number);
        $nextSalesReturnNumber = InvoiceSetting::getNextSalesReturnNumber(date('Y-m-d'), $this->sales_return_prefix, $this->starting_number);
        $nextPurchaseReturnNumber = InvoiceSetting::getNextPurchaseReturnNumber(date('Y-m-d'), $this->purchase_return_prefix, $this->starting_number);
        $nextCustomerPaymentNumber = InvoiceSetting::getNextCustomerPaymentNumber(date('Y-m-d'), $this->customer_payment_prefix, $this->starting_number);
        $nextSupplierPaymentNumber = InvoiceSetting::getNextSupplierPaymentNumber(date('Y-m-d'), $this->supplier_payment_prefix, $this->starting_number);

        // Next year starting previews for all documents
        $formatNextYear = function($prefix, $default = 'DOC') use ($nextYear) {
            $clean = rtrim($prefix ?: $default, '-');
            if (empty($clean)) $clean = $default;
            return $clean . '-' . $nextYear . '-' . str_pad((string)($this->starting_number ?: 1), 4, '0', STR_PAD_LEFT);
        };

        return view('livewire.settings.invoice-settings', [
            'currentYear' => $currentYear,
            'previousYear' => $previousYear,
            'nextYear' => $nextYear,
            'nextCurrentYearNumber' => $nextCurrentYearNumber,
            'currentYearSalesCount' => $currentYearSalesCount,
            'currentYearLastNumber' => $currentYearLastNumber,
            'previousYearSalesCount' => $previousYearSalesCount,
            'previousYearLastNumber' => $previousYearLastNumber,
            'nextPurchaseNumber' => $nextPurchaseNumber,
            'nextSalesReturnNumber' => $nextSalesReturnNumber,
            'nextPurchaseReturnNumber' => $nextPurchaseReturnNumber,
            'nextCustomerPaymentNumber' => $nextCustomerPaymentNumber,
            'nextSupplierPaymentNumber' => $nextSupplierPaymentNumber,
            'nextYearSales' => $formatNextYear($this->invoice_prefix, 'INV'),
            'nextYearPurchase' => $formatNextYear($this->purchase_prefix, 'PUR'),
            'nextYearSalesReturn' => $formatNextYear($this->sales_return_prefix, 'SR'),
            'nextYearPurchaseReturn' => $formatNextYear($this->purchase_return_prefix, 'PR'),
            'nextYearCustomerPayment' => $formatNextYear($this->customer_payment_prefix, 'REC'),
            'nextYearSupplierPayment' => $formatNextYear($this->supplier_payment_prefix, 'PAY'),
        ])->layout('layouts.app', ['title' => 'Invoice & Numbering Settings']);
    }
}
