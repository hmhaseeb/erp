<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;

class CompanySettings extends Component
{
    public $company_name, $legal_name, $address, $city, $country, $phone, $mobile, $email, $website, $trn_number, $currency, $currency_symbol, $default_vat_percent;
    public $selected_preset = 'AED';

    public static array $commonCurrencies = [
        'AED' => ['name' => 'United Arab Emirates Dirham', 'symbol' => 'AED'],
        'USD' => ['name' => 'United States Dollar', 'symbol' => '$'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR'],
        'QAR' => ['name' => 'Qatari Riyal', 'symbol' => 'QAR'],
        'OMR' => ['name' => 'Omani Rial', 'symbol' => 'OMR'],
        'KWD' => ['name' => 'Kuwaiti Dinar', 'symbol' => 'KWD'],
        'BHD' => ['name' => 'Bahraini Dinar', 'symbol' => 'BHD'],
        'PKR' => ['name' => 'Pakistani Rupee', 'symbol' => 'Rs.'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'CA$'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'AU$'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$'],
        'MYR' => ['name' => 'Malaysian Ringgit', 'symbol' => 'RM'],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥'],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥'],
        'TRY' => ['name' => 'Turkish Lira', 'symbol' => '₺'],
        'EGP' => ['name' => 'Egyptian Pound', 'symbol' => 'EGP'],
    ];

    public function mount()
    {
        $setting = CompanySetting::first();
        if ($setting) {
            $this->company_name = $setting->company_name;
            $this->legal_name = $setting->legal_name;
            $this->address = $setting->address;
            $this->city = $setting->city;
            $this->country = $setting->country;
            $this->phone = $setting->phone;
            $this->mobile = $setting->mobile;
            $this->email = $setting->email;
            $this->website = $setting->website;
            $this->trn_number = $setting->trn_number;
            $this->currency = $setting->currency ?: 'AED';
            $this->currency_symbol = $setting->currency_symbol ?: ($setting->currency ?: 'AED');
            $this->default_vat_percent = $setting->default_vat_percent;
        } else {
            $this->currency = 'AED';
            $this->currency_symbol = 'AED';
            $this->default_vat_percent = 5.00;
        }

        $code = strtoupper(trim($this->currency));
        if (isset(self::$commonCurrencies[$code])) {
            $this->selected_preset = $code;
        } else {
            $this->selected_preset = 'CUSTOM';
        }
    }

    public function updatedSelectedPreset($value)
    {
        if ($value !== 'CUSTOM' && isset(self::$commonCurrencies[$value])) {
            $this->currency = $value;
            $this->currency_symbol = self::$commonCurrencies[$value]['symbol'];
        }
    }

    public function updatedCurrency($value)
    {
        $code = strtoupper(trim($value));
        $this->currency = $code;

        if (isset(self::$commonCurrencies[$code])) {
            $this->selected_preset = $code;
            if (empty($this->currency_symbol)) {
                $this->currency_symbol = self::$commonCurrencies[$code]['symbol'];
            }
        } else {
            $this->selected_preset = 'CUSTOM';
        }
    }

    public function saveSettings()
    {
        $this->currency = strtoupper(trim($this->currency));
        $this->currency_symbol = trim($this->currency_symbol) ?: $this->currency;

        $this->validate([
            'company_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'default_vat_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        CompanySetting::updateOrCreate(
            ['id' => 1],
            [
                'company_name' => $this->company_name,
                'legal_name' => $this->legal_name,
                'address' => $this->address,
                'city' => $this->city,
                'country' => $this->country,
                'phone' => $this->phone,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'website' => $this->website,
                'trn_number' => $this->trn_number,
                'currency' => $this->currency,
                'currency_symbol' => $this->currency_symbol,
                'default_vat_percent' => $this->default_vat_percent,
            ]
        );

        \App\Services\SettingsService::clearCache();

        session()->flash('success', 'Company settings updated successfully.');

        $this->dispatch('check-and-open-setup-wizard');
    }

    public function render()
    {
        return view('livewire.settings.company-settings', [
            'commonCurrencies' => self::$commonCurrencies,
        ])->layout('layouts.app', ['title' => 'Company Settings']);
    }
}
