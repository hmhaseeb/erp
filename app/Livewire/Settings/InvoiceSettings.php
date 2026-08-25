<?php

namespace App\Livewire\Settings;

use App\Models\InvoiceSetting;
use Livewire\Component;

class InvoiceSettings extends Component
{
    public $invoice_prefix, $starting_number, $purchase_prefix, $sales_return_prefix, $purchase_return_prefix;
    public $customer_payment_prefix, $supplier_payment_prefix, $invoice_footer, $terms_conditions, $payment_terms, $bank_details, $paper_size;

    public function mount()
    {
        $setting = InvoiceSetting::first();
        if ($setting) {
            $this->invoice_prefix = $setting->invoice_prefix;
            $this->starting_number = $setting->starting_number;
            $this->purchase_prefix = $setting->purchase_prefix;
            $this->sales_return_prefix = $setting->sales_return_prefix;
            $this->purchase_return_prefix = $setting->purchase_return_prefix;
            $this->customer_payment_prefix = $setting->customer_payment_prefix;
            $this->supplier_payment_prefix = $setting->supplier_payment_prefix;
            $this->invoice_footer = $setting->invoice_footer;
            $this->terms_conditions = $setting->terms_conditions;
            $this->payment_terms = $setting->payment_terms;
            $this->bank_details = $setting->bank_details;
            $this->paper_size = $setting->paper_size;
        }
    }

    public function saveSettings()
    {
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

        \App\Services\SettingsService::clearCache();

        session()->flash('success', 'Invoice settings updated.');
    }

    public function render()
    {
        return view('livewire.settings.invoice-settings')
            ->layout('layouts.app', ['title' => 'Invoice Settings']);
    }
}
