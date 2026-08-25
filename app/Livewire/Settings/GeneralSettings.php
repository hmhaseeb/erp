<?php

namespace App\Livewire\Settings;

use App\Models\Account;
use App\Models\GeneralSetting;
use Livewire\Component;

class GeneralSettings extends Component
{
    public $date_format, $time_zone, $decimal_places, $default_cash_account_id, $default_bank_account_id;
    public $product_prefix, $supplier_prefix, $customer_prefix, $allow_negative_stock = false;

    public function mount()
    {
        $setting = GeneralSetting::first();
        if ($setting) {
            $this->date_format = $setting->date_format;
            $this->time_zone = $setting->time_zone;
            $this->decimal_places = $setting->decimal_places;
            $this->default_cash_account_id = $setting->default_cash_account_id;
            $this->default_bank_account_id = $setting->default_bank_account_id;
            $this->product_prefix = $setting->product_prefix;
            $this->supplier_prefix = $setting->supplier_prefix;
            $this->customer_prefix = $setting->customer_prefix;
            $this->allow_negative_stock = (bool) $setting->allow_negative_stock;
        }
    }

    public function saveSettings()
    {
        GeneralSetting::updateOrCreate(
            ['id' => 1],
            [
                'date_format' => $this->date_format,
                'time_zone' => $this->time_zone,
                'decimal_places' => $this->decimal_places,
                'default_cash_account_id' => $this->default_cash_account_id ?: null,
                'default_bank_account_id' => $this->default_bank_account_id ?: null,
                'product_prefix' => $this->product_prefix,
                'supplier_prefix' => $this->supplier_prefix,
                'customer_prefix' => $this->customer_prefix,
                'allow_negative_stock' => $this->allow_negative_stock,
            ]
        );

        \App\Services\SettingsService::clearCache();

        session()->flash('success', 'General settings saved.');
    }

    public function render()
    {
        $accounts = Account::all();
        return view('livewire.settings.general-settings', ['accounts' => $accounts])
            ->layout('layouts.app', ['title' => 'General System Settings']);
    }
}
