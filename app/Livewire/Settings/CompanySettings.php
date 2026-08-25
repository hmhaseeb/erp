<?php

namespace App\Livewire\Settings;

use App\Models\CompanySetting;
use Livewire\Component;

class CompanySettings extends Component
{
    public $company_name, $legal_name, $address, $city, $country, $phone, $mobile, $email, $website, $trn_number, $currency, $currency_symbol, $default_vat_percent;

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
            $this->currency = $setting->currency;
            $this->currency_symbol = $setting->currency_symbol;
            $this->default_vat_percent = $setting->default_vat_percent;
        }
    }

    public function saveSettings()
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'currency' => 'required|string',
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
    }

    public function render()
    {
        return view('livewire.settings.company-settings')
            ->layout('layouts.app', ['title' => 'Company Settings']);
    }
}
