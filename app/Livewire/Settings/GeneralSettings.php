<?php

namespace App\Livewire\Settings;

use App\Models\Account;
use App\Models\GeneralSetting;
use Carbon\Carbon;
use Livewire\Component;

class GeneralSettings extends Component
{
    public $date_format = 'Y-m-d';
    public $time_zone = 'Asia/Dubai';
    public $decimal_places = 2;
    public $default_cash_account_id;
    public $default_bank_account_id;
    public $product_prefix = 'PRD-';
    public $supplier_prefix = 'SUP-';
    public $customer_prefix = 'CUST-';
    public $allow_negative_stock = false;

    public function mount()
    {
        $setting = GeneralSetting::first();
        if ($setting) {
            $this->date_format = $setting->date_format ?: 'Y-m-d';
            $this->time_zone = $setting->time_zone ?: 'Asia/Dubai';
            $this->decimal_places = $setting->decimal_places ?? 2;
            $this->default_cash_account_id = $setting->default_cash_account_id;
            $this->default_bank_account_id = $setting->default_bank_account_id;
            $this->product_prefix = $setting->product_prefix ?: 'PRD-';
            $this->supplier_prefix = $setting->supplier_prefix ?: 'SUP-';
            $this->customer_prefix = $setting->customer_prefix ?: 'CUST-';
            $this->allow_negative_stock = (bool) $setting->allow_negative_stock;
        } else {
            $this->loadDefaults(false);
        }

        // Auto-select first cash and bank accounts if not already assigned
        if (!$this->default_cash_account_id) {
            $firstCash = Account::where('type', 'Cash')->first();
            if ($firstCash) {
                $this->default_cash_account_id = $firstCash->id;
            }
        }
        if (!$this->default_bank_account_id) {
            $firstBank = Account::where('type', 'Bank')->first();
            if ($firstBank) {
                $this->default_bank_account_id = $firstBank->id;
            }
        }
    }

    public function loadDefaults(bool $flash = true)
    {
        $this->date_format = 'Y-m-d';
        $this->time_zone = 'Asia/Dubai';
        $this->decimal_places = 2;
        $this->product_prefix = 'PRD-';
        $this->supplier_prefix = 'SUP-';
        $this->customer_prefix = 'CUST-';
        $this->allow_negative_stock = false;

        $firstCash = Account::where('type', 'Cash')->first();
        if ($firstCash) {
            $this->default_cash_account_id = $firstCash->id;
        }

        $firstBank = Account::where('type', 'Bank')->first();
        if ($firstBank) {
            $this->default_bank_account_id = $firstBank->id;
        }

        if ($flash) {
            session()->flash('success', 'Recommended ERP default settings loaded.');
        }
    }

    public function saveSettings()
    {
        $this->validate([
            'date_format' => 'required|string|max:50',
            'time_zone' => 'required|string|max:100',
            'decimal_places' => 'required|integer|min:0|max:4',
            'product_prefix' => 'nullable|string|max:20',
            'supplier_prefix' => 'nullable|string|max:20',
            'customer_prefix' => 'nullable|string|max:20',
            'default_cash_account_id' => 'nullable|exists:accounts,id',
            'default_bank_account_id' => 'nullable|exists:accounts,id',
        ]);

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

        session()->flash('success', 'General system settings saved successfully.');

        $this->dispatch('check-and-open-setup-wizard');
    }

    public function getFormattedCurrentDateProperty(): string
    {
        try {
            $tz = $this->time_zone ?: config('app.timezone', 'Asia/Dubai');
            $format = $this->date_format ?: 'Y-m-d';
            return Carbon::now($tz)->format($format);
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    public function getFormattedCurrentTimeProperty(): string
    {
        try {
            $tz = $this->time_zone ?: config('app.timezone', 'Asia/Dubai');
            return Carbon::now($tz)->format('h:i:s A (T, P)');
        } catch (\Exception $e) {
            return now()->format('h:i:s A');
        }
    }

    public function getFullDateTimePreviewProperty(): string
    {
        try {
            $tz = $this->time_zone ?: config('app.timezone', 'Asia/Dubai');
            return Carbon::now($tz)->isoFormat('dddd, D MMMM YYYY • h:mm:ss A');
        } catch (\Exception $e) {
            return now()->toDayDateTimeString();
        }
    }

    public static function getTimezoneList(): array
    {
        return [
            'Middle East & Gulf' => [
                'Asia/Dubai' => 'Asia/Dubai (UAE, Dubai, Abu Dhabi • GST UTC+04:00)',
                'Asia/Riyadh' => 'Asia/Riyadh (Saudi Arabia • AST UTC+03:00)',
                'Asia/Qatar' => 'Asia/Qatar (Doha • AST UTC+03:00)',
                'Asia/Kuwait' => 'Asia/Kuwait (Kuwait City • AST UTC+03:00)',
                'Asia/Muscat' => 'Asia/Muscat (Oman • GST UTC+04:00)',
                'Asia/Bahrain' => 'Asia/Bahrain (Manama • AST UTC+03:00)',
                'Africa/Cairo' => 'Africa/Cairo (Egypt • EET UTC+03:00)',
                'Asia/Amman' => 'Asia/Amman (Jordan • UTC+03:00)',
                'Asia/Beirut' => 'Asia/Beirut (Lebanon • UTC+03:00)',
            ],
            'South Asia' => [
                'Asia/Karachi' => 'Asia/Karachi (Pakistan • PKT UTC+05:00)',
                'Asia/Kolkata' => 'Asia/Kolkata (India • IST UTC+05:30)',
                'Asia/Dhaka' => 'Asia/Dhaka (Bangladesh • BST UTC+06:00)',
                'Asia/Colombo' => 'Asia/Colombo (Sri Lanka • IST UTC+05:30)',
                'Asia/Kathmandu' => 'Asia/Kathmandu (Nepal • NPT UTC+05:45)',
            ],
            'Southeast & East Asia' => [
                'Asia/Singapore' => 'Asia/Singapore (Singapore • SGT UTC+08:00)',
                'Asia/Kuala_Lumpur' => 'Asia/Kuala_Lumpur (Malaysia • MYT UTC+08:00)',
                'Asia/Bangkok' => 'Asia/Bangkok (Thailand, Vietnam • ICT UTC+07:00)',
                'Asia/Jakarta' => 'Asia/Jakarta (Indonesia • WIB UTC+07:00)',
                'Asia/Hong_Kong' => 'Asia/Hong_Kong (Hong Kong • HKT UTC+08:00)',
                'Asia/Shanghai' => 'Asia/Shanghai (China • CST UTC+08:00)',
                'Asia/Tokyo' => 'Asia/Tokyo (Japan • JST UTC+09:00)',
                'Asia/Seoul' => 'Asia/Seoul (South Korea • KST UTC+09:00)',
            ],
            'Europe & United Kingdom' => [
                'Europe/London' => 'Europe/London (United Kingdom • GMT/BST UTC+01:00)',
                'Europe/Paris' => 'Europe/Paris (France • CET UTC+02:00)',
                'Europe/Berlin' => 'Europe/Berlin (Germany • CET UTC+02:00)',
                'Europe/Rome' => 'Europe/Rome (Italy • CET UTC+02:00)',
                'Europe/Madrid' => 'Europe/Madrid (Spain • CET UTC+02:00)',
                'Europe/Amsterdam' => 'Europe/Amsterdam (Netherlands • CET UTC+02:00)',
                'Europe/Istanbul' => 'Europe/Istanbul (Turkey • TRT UTC+03:00)',
                'Europe/Athens' => 'Europe/Athens (Greece • EET UTC+03:00)',
            ],
            'Americas' => [
                'America/New_York' => 'America/New_York (US Eastern • EDT UTC-04:00)',
                'America/Chicago' => 'America/Chicago (US Central • CDT UTC-05:00)',
                'America/Denver' => 'America/Denver (US Mountain • MDT UTC-06:00)',
                'America/Los_Angeles' => 'America/Los_Angeles (US Pacific • PDT UTC-07:00)',
                'America/Toronto' => 'America/Toronto (Canada Eastern • EDT UTC-04:00)',
                'America/Vancouver' => 'America/Vancouver (Canada Pacific • PDT UTC-07:00)',
                'America/Sao_Paulo' => 'America/Sao_Paulo (Brazil • BRT UTC-03:00)',
            ],
            'Australia & Pacific' => [
                'Australia/Sydney' => 'Australia/Sydney (Sydney • AEST UTC+10:00)',
                'Australia/Melbourne' => 'Australia/Melbourne (Melbourne • AEST UTC+10:00)',
                'Australia/Perth' => 'Australia/Perth (Perth • AWST UTC+08:00)',
                'Pacific/Auckland' => 'Pacific/Auckland (New Zealand • NZST UTC+12:00)',
            ],
            'Universal Standard' => [
                'UTC' => 'UTC (Universal Coordinated Time • UTC+00:00)',
            ],
        ];
    }

    public function render()
    {
        $accounts = Account::where('status', true)->get();
        return view('livewire.settings.general-settings', [
            'accounts' => $accounts,
            'timezonesGrouped' => self::getTimezoneList(),
        ])->layout('layouts.app', ['title' => 'General System Settings']);
    }
}
